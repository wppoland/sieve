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
    private const LOCK = 'sieve_indexing_lock';

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
     * Build the index once per version so facets work immediately, including for
     * products that existed before the plugin was activated. The stored option
     * holds the version the index was last built for; a backfill runs whenever
     * the installed VERSION is newer (so upgrading to a release that adds new row
     * types, e.g. the 0.6.0 '_search' tokens, rebuilds once) and only once
     * products exist.
     */
    public function ensureInitialIndex(): void
    {
        $stored = (string) get_option('sieve_index_ready', '0');
        if (version_compare($stored, VERSION, '>=')) {
            return;
        }

        $counts = wp_count_posts('product');
        $published = isset($counts->publish) ? (int) $counts->publish : 0;
        if ($published < 1) {
            return;
        }

        // A full indexAll() truncates and rebuilds; guard against a concurrent
        // admin_init request doing the same at the same moment (which would race
        // on the TRUNCATE). The lock auto-expires so a fatal mid-build cannot
        // wedge the backfill permanently.
        if (get_transient(self::LOCK)) {
            return;
        }
        set_transient(self::LOCK, 1, 5 * MINUTE_IN_SECONDS);

        $this->indexer->indexAll();
        update_option('sieve_index_ready', VERSION);

        delete_transient(self::LOCK);
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
