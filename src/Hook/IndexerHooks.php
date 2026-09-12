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
     * This schedules the work rather than doing it. Indexing the whole catalog
     * inside admin_init made every admin page load pay for it and timed the
     * request out on a large catalog. The one exception is a site with wp-cron
     * switched off, where scheduling alone can mean the index is never built at
     * all; see below.
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

        // With DISABLE_WP_CRON there may be no cron at all. The documented
        // pairing is a system cron, but nothing here can see whether one was
        // actually set up, and if it was not, a scheduled event never runs and
        // the index would stay empty forever with nothing to say so. Index one
        // batch inline instead: one batch, not the catalog, and the cursor
        // means the next admin load carries on from there. If a system cron
        // does exist it picks up the event this books and finishes sooner.
        if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
            $this->runBackfill();
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
        // Guard against two workers walking the catalog at once (they would race
        // on the cursor and index the same batch twice). The lock auto-expires,
        // so a fatal mid-batch cannot wedge the backfill permanently.
        if (get_transient(self::LOCK)) {
            // A tick that dies mid-batch leaves the lock behind. Returning here
            // without booking anything would then stall the backfill for good,
            // because nothing else reschedules it. Come back once the lock can
            // have expired.
            if (! wp_next_scheduled(self::BACKFILL_HOOK)) {
                wp_schedule_single_event(time() + MINUTE_IN_SECONDS, self::BACKFILL_HOOK);
            }
            return;
        }
        set_transient(self::LOCK, 1, 5 * MINUTE_IN_SECONDS);

        $ids = $this->indexer->indexBatch($this->cursor());

        if ([] === $ids) {
            // Every published product has had its rows replaced in place by now.
            // What is left over belongs to products that were deleted or
            // unpublished, so dropping those completes the rebuild. The index was
            // readable the whole way through: no TRUNCATE, no blackout.
            $this->indexer->removeOrphans();

            delete_option(self::CURSOR);
            update_option('sieve_index_ready', VERSION);
            delete_transient(self::LOCK);
            return;
        }

        update_option(self::CURSOR, VERSION . ':' . (int) end($ids), false);
        delete_transient(self::LOCK);

        wp_schedule_single_event(time(), self::BACKFILL_HOOK);
    }

    /**
     * Highest product ID indexed so far by a backfill of THIS version, or 0 to
     * start from the beginning.
     *
     * The stored value carries the version that wrote it. A cursor left behind
     * by a backfill interrupted under an older release must not be resumed: the
     * products before it still hold rows built by that release, and finishing
     * the walk would then set sieve_index_ready to the new version while those
     * rows stay stale forever. A version that does not match means start over.
     */
    private function cursor(): int
    {
        $stored = (string) get_option(self::CURSOR, '');
        [$version, $id] = array_pad(explode(':', $stored, 2), 2, '');

        return VERSION === $version ? (int) $id : 0;
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
