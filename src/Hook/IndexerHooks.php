<?php

declare(strict_types=1);

namespace Sieve\Hook;

defined('ABSPATH') || exit;

use Sieve\Contract\HasHooks;
use Sieve\Service\ProductIndexer;

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
