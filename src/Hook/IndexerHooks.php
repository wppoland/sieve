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
    private const CURSOR = 'sieve_index_cursor';

    public const BACKFILL_HOOK = 'sieve_index_backfill';

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
        add_action(self::BACKFILL_HOOK, [$this, 'runBackfill']);
    }

    /**
     * Build the index once per version so facets work immediately, including for
     * products that existed before the plugin was activated. The stored option
     * holds the version the index was last built for; a backfill runs whenever
     * the installed VERSION is newer (so upgrading to a release that adds new row
     * types, e.g. the 0.6.0 '_search' tokens, rebuilds once) and only once
     * products exist.
     *
     * This only schedules the work. Indexing the whole catalog inside admin_init
     * made every admin page load pay for it and timed the request out on a large
     * catalog.
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

        if (wp_next_scheduled(self::BACKFILL_HOOK)) {
            return;
        }

        wp_schedule_single_event(time(), self::BACKFILL_HOOK);
    }

    /**
     * Index one batch and hand the rest to the next cron tick. The cursor option
     * holds the highest product ID indexed so far, so a run interrupted by a
     * timeout or a fatal resumes where it stopped instead of starting over.
     *
     * ponytail: one batch per cron tick is the deliberate ceiling. It keeps each
     * request small and needs no dependency; the cost is that a huge catalog
     * takes many ticks to finish. Upgrade path if that ever bites: enqueue the
     * batches with Action Scheduler (as_enqueue_async_action), which runs them
     * back to back in its own queue runner.
     */
    public function runBackfill(): void
    {
        // Guard against two cron workers walking the catalog at once (they would
        // race on the TRUNCATE and on the cursor). The lock auto-expires so a
        // fatal mid-batch cannot wedge the backfill permanently.
        if (get_transient(self::LOCK)) {
            return;
        }
        set_transient(self::LOCK, 1, 5 * MINUTE_IN_SECONDS);

        $cursor = get_option(self::CURSOR, false);
        if (false === $cursor) {
            // Start of a rebuild: clear stale rows, as the old in-request
            // indexAll() did before walking the catalog.
            $this->indexer->resetIndex();
        }

        $ids = $this->indexer->indexBatch((int) $cursor);

        if ([] === $ids) {
            delete_option(self::CURSOR);
            update_option('sieve_index_ready', VERSION);
            delete_transient(self::LOCK);
            return;
        }

        update_option(self::CURSOR, (int) end($ids), false);
        delete_transient(self::LOCK);

        wp_schedule_single_event(time(), self::BACKFILL_HOOK);
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
