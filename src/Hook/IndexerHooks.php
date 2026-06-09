<?php

declare(strict_types=1);

namespace Sieve\Hook;

defined('ABSPATH') || exit;

use Sieve\Contract\HasHooks;
use Sieve\Service\ProductIndexer;

use const Sieve\VERSION;

/**
 * Keeps the index in sync with the catalog: re-index a product whenever it is
 * created or updated, and drop it when trashed or deleted.
 */
final class IndexerHooks implements HasHooks
{
    public function __construct(private readonly ProductIndexer $indexer)
    {
    }

    public function registerHooks(): void
    {
        add_action('woocommerce_update_product', [$this, 'onSave']);
        add_action('woocommerce_new_product', [$this, 'onSave']);
        add_action('before_delete_post', [$this, 'onDelete']);
        add_action('wp_trash_post', [$this, 'onDelete']);
        add_action('admin_init', [$this, 'ensureInitialIndex']);
    }

    /**
     * Build the index once after activation so facets work immediately, including
     * for products that existed before the plugin was activated. Runs a single
     * time (guarded by an autoloaded option) and only once products exist.
     */
    public function ensureInitialIndex(): void
    {
        if (get_option('sieve_index_ready')) {
            return;
        }

        $counts = wp_count_posts('product');
        $published = isset($counts->publish) ? (int) $counts->publish : 0;
        if ($published < 1) {
            return;
        }

        $this->indexer->indexAll();
        update_option('sieve_index_ready', VERSION);
    }

    public function onSave(int $productId): void
    {
        $this->indexer->indexProduct($productId);
    }

    public function onDelete(int $postId): void
    {
        if ('product' === get_post_type($postId)) {
            $this->indexer->removeProduct($postId);
        }
    }
}
