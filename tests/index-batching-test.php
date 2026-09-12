<?php

declare(strict_types=1);

/**
 * Fails if the catalog walk stops being batched, or if a rebuild goes back to
 * emptying the index before it refills it.
 *
 * Sieve used to index the whole catalog inside admin_init, which made every
 * admin page load pay for it. Batching that across cron ticks then introduced a
 * second problem: clearing the table up front turned a one-request blackout
 * into one that lasted the whole rebuild. This asserts all of it: bounded
 * batches with a keyset cursor, admin_init not indexing inline where cron can
 * do it, a rebuild that leaves the old rows readable until it replaces them, a
 * cursor that cannot be resumed by a different version, a blocked tick that
 * still books a retry, and a site with wp-cron switched off still getting an
 * index.
 *
 * No framework on purpose: plain PHP with WordPress stubbed.
 *
 *   php tests/index-batching-test.php
 */

namespace {
    define('ABSPATH', __DIR__);
    define('MINUTE_IN_SECONDS', 60);

    const SIEVE_TEST_PRODUCT_IDS = [3, 8, 9, 21, 34, 55, 90];

    // An index row left behind by a product that has since been deleted.
    const SIEVE_TEST_GHOST_ID = 999;

    // Read the version from the plugin header instead of repeating it here, so
    // the next release bump cannot quietly break this test.
    if (! preg_match(
        "/const VERSION\s*=\s*'([^']+)'/",
        (string) file_get_contents(__DIR__ . '/../sieve.php'),
        $sieveVersionMatch
    )) {
        fwrite(STDERR, "cannot read VERSION from sieve.php\n");
        exit(1);
    }
    define('SIEVE_TEST_VERSION', $sieveVersionMatch[1]);

    $GLOBALS['sieve_test'] = [
        'selects' => [],
        'queries' => [],
        'indexed' => [],
        'rows' => [],
        'options' => [],
        'transients' => [],
        'scheduled' => [],
        'filters' => [],
        'cache_flushes' => 0,
    ];

    final class Sieve_Test_Wpdb
    {
        public string $prefix = 'wp_';
        public string $posts = 'wp_posts';

        /** @var array<int, string> */
        public array $queries = [];

        /** @param array<int, mixed>|mixed $args */
        public function prepare(string $query, $args = null, ...$rest): string
        {
            $values = is_array($args) ? $args : array_merge([$args], $rest);

            return preg_replace_callback(
                '/%[dsf]/',
                static function () use (&$values): string {
                    return (string) array_shift($values);
                },
                $query
            );
        }

        /** @return array<int, string> */
        public function get_col(string $sql): array
        {
            $GLOBALS['sieve_test']['selects'][] = $sql;

            if (! preg_match('/ID > (\d+).*LIMIT (\d+)/s', $sql, $m)) {
                throw new RuntimeException('unbatched product query: ' . $sql);
            }

            $after = (int) $m[1];
            $limit = (int) $m[2];

            $ids = array_values(array_filter(
                SIEVE_TEST_PRODUCT_IDS,
                static fn (int $id): bool => $id > $after
            ));

            return array_map('strval', array_slice($ids, 0, $limit));
        }

        /** @param array<string, mixed> $data */
        public function insert(string $table, array $data, array $format = []): int
        {
            $GLOBALS['sieve_test']['rows'][] = $data;
            return 1;
        }

        /** @param array<string, mixed> $where */
        public function delete(string $table, array $where, array $format = []): int
        {
            $before = count($GLOBALS['sieve_test']['rows']);

            $GLOBALS['sieve_test']['rows'] = array_values(array_filter(
                $GLOBALS['sieve_test']['rows'],
                static fn (array $row): bool => (int) $row['object_id'] !== (int) $where['object_id']
            ));

            return $before - count($GLOBALS['sieve_test']['rows']);
        }

        public function query(string $sql): int
        {
            $GLOBALS['sieve_test']['queries'][] = $sql;

            if (str_starts_with($sql, 'TRUNCATE')) {
                $GLOBALS['sieve_test']['rows'] = [];
                return 1;
            }

            if (str_starts_with($sql, 'DELETE')) {
                $before = count($GLOBALS['sieve_test']['rows']);

                $GLOBALS['sieve_test']['rows'] = array_values(array_filter(
                    $GLOBALS['sieve_test']['rows'],
                    static fn (array $row): bool => in_array(
                        (int) $row['object_id'],
                        SIEVE_TEST_PRODUCT_IDS,
                        true
                    )
                ));

                return $before - count($GLOBALS['sieve_test']['rows']);
            }

            return 1;
        }
    }

    $GLOBALS['wpdb'] = new Sieve_Test_Wpdb();

    class WC_Product
    {
        public function __construct(private int $id)
        {
        }

        public function get_id(): int
        {
            return $this->id;
        }

        public function get_status(): string
        {
            return 'publish';
        }

        public function get_price(): string
        {
            return '9.99';
        }

        public function get_stock_status(): string
        {
            return 'instock';
        }

        public function is_on_sale(): bool
        {
            return false;
        }

        public function get_average_rating(): string
        {
            return '0';
        }

        public function get_catalog_visibility(): string
        {
            return 'visible';
        }

        public function get_sku(): string
        {
            return 'sku-' . $this->id;
        }

        public function get_name(): string
        {
            return 'Product ' . $this->id;
        }
    }

    function wc_get_product(int $id): WC_Product
    {
        $GLOBALS['sieve_test']['indexed'][] = $id;
        return new WC_Product($id);
    }

    function wc_get_attribute_taxonomy_names(): array
    {
        return [];
    }

    function get_the_terms(int $id, string $taxonomy)
    {
        return false;
    }

    function apply_filters(string $hook, $value, ...$args)
    {
        $cb = $GLOBALS['sieve_test']['filters'][$hook] ?? null;
        return null === $cb ? $value : $cb($value);
    }

    function add_action(string $hook, $callback, int $priority = 10, int $args = 1): void
    {
    }

    function get_option(string $key, $default = false)
    {
        return $GLOBALS['sieve_test']['options'][$key] ?? $default;
    }

    function update_option(string $key, $value, $autoload = null): bool
    {
        $GLOBALS['sieve_test']['options'][$key] = $value;
        return true;
    }

    function delete_option(string $key): bool
    {
        unset($GLOBALS['sieve_test']['options'][$key]);
        return true;
    }

    function get_transient(string $key)
    {
        return $GLOBALS['sieve_test']['transients'][$key] ?? false;
    }

    function set_transient(string $key, $value, int $ttl = 0): bool
    {
        $GLOBALS['sieve_test']['transients'][$key] = $value;
        return true;
    }

    function delete_transient(string $key): bool
    {
        unset($GLOBALS['sieve_test']['transients'][$key]);
        return true;
    }

    function wp_count_posts(string $type): object
    {
        return (object) ['publish' => count(SIEVE_TEST_PRODUCT_IDS)];
    }

    function wp_next_scheduled(string $hook, array $args = [])
    {
        return $GLOBALS['sieve_test']['scheduled'][$hook] ?? false;
    }

    function wp_schedule_single_event(int $timestamp, string $hook, array $args = []): bool
    {
        $GLOBALS['sieve_test']['scheduled'][$hook] = $timestamp;
        return true;
    }

    function wp_clear_scheduled_hook(string $hook, array $args = []): int
    {
        unset($GLOBALS['sieve_test']['scheduled'][$hook]);
        return 1;
    }

    function wp_using_ext_object_cache(): bool
    {
        return false;
    }

    function wp_cache_flush_runtime(): bool
    {
        $GLOBALS['sieve_test']['cache_flushes']++;
        return true;
    }
}

namespace Sieve {
    const VERSION = \SIEVE_TEST_VERSION;
}

namespace {
    require __DIR__ . '/../vendor/autoload.php';

    $failures = 0;

    function sieve_assert(bool $ok, string $label): void
    {
        global $failures;
        if ($ok) {
            echo "PASS: {$label}\n";
            return;
        }
        echo "FAIL: {$label}\n";
        $failures++;
    }

    function sieve_reset(): void
    {
        $GLOBALS['sieve_test']['selects'] = [];
        $GLOBALS['sieve_test']['queries'] = [];
        $GLOBALS['sieve_test']['indexed'] = [];
        $GLOBALS['sieve_test']['rows'] = [];
        $GLOBALS['sieve_test']['options'] = [];
        $GLOBALS['sieve_test']['transients'] = [];
        $GLOBALS['sieve_test']['scheduled'] = [];
        $GLOBALS['sieve_test']['cache_flushes'] = 0;
    }

    /**
     * Rows currently in the fake index table for one object.
     *
     * @return array<int, array<string, mixed>>
     */
    function sieve_rows_for(int $objectId): array
    {
        return array_values(array_filter(
            $GLOBALS['sieve_test']['rows'],
            static fn (array $row): bool => (int) $row['object_id'] === $objectId
        ));
    }

    /** Fill the table with the rows a previous index build left behind. */
    function sieve_seed_old_index(): void
    {
        foreach (array_merge(SIEVE_TEST_PRODUCT_IDS, [SIEVE_TEST_GHOST_ID]) as $id) {
            $GLOBALS['sieve_test']['rows'][] = [
                'object_id' => $id,
                'facet_slug' => 'product_cat',
                'value' => 'old',
                'value_num' => null,
            ];
        }
    }

    /** The product ID half of the stored cursor, 0 when there is none. */
    function sieve_cursor_id(): int
    {
        $parts = explode(':', (string) get_option('sieve_index_cursor', ''), 2);

        return isset($parts[1]) ? (int) $parts[1] : 0;
    }

    function sieve_has_old_rows(): bool
    {
        foreach ($GLOBALS['sieve_test']['rows'] as $row) {
            if ('old' === $row['value']) {
                return true;
            }
        }

        return false;
    }

    $hook = \Sieve\Hook\IndexerHooks::BACKFILL_HOOK;

    // Batch size is a filter, not a setting screen.
    $GLOBALS['sieve_test']['filters']['sieve_index_batch_size'] = static fn (): int => 3;

    $indexer = new \Sieve\Service\ProductIndexer(new \Sieve\Repository\IndexRepository());
    $hooks = new \Sieve\Hook\IndexerHooks($indexer);

    // 1. indexAll walks the catalog in bounded batches, every product once, in ID order.
    sieve_reset();
    sieve_seed_old_index();
    $count = $indexer->indexAll();
    $expected = SIEVE_TEST_PRODUCT_IDS;

    sieve_assert(7 === $count, 'indexAll reports every product');
    sieve_assert($expected === $GLOBALS['sieve_test']['indexed'], 'every product indexed once, in ID order');
    sieve_assert(4 === count($GLOBALS['sieve_test']['selects']), 'seven products at batch size three take four queries');
    sieve_assert(3 === $GLOBALS['sieve_test']['cache_flushes'], 'indexAll drops the request object cache between batches');
    sieve_assert(! sieve_has_old_rows(), 'indexAll replaces every stale row');
    sieve_assert([] === sieve_rows_for(SIEVE_TEST_GHOST_ID), 'indexAll drops rows of products that no longer exist');

    $overLimit = array_filter(
        $GLOBALS['sieve_test']['selects'],
        static function (string $sql): bool {
            preg_match('/LIMIT (\d+)/', $sql, $m);
            return ! isset($m[1]) || (int) $m[1] > 3;
        }
    );
    sieve_assert([] === $overLimit, 'no query asks for more than the filtered batch size');

    // 2. admin_init only schedules: it must not read a single product.
    sieve_reset();
    $hooks->ensureInitialIndex();

    sieve_assert([] === $GLOBALS['sieve_test']['indexed'], 'admin_init indexes nothing inline');
    sieve_assert([] === $GLOBALS['sieve_test']['selects'], 'admin_init runs no catalog query');
    sieve_assert(false !== wp_next_scheduled($hook), 'admin_init schedules the backfill');

    // A second admin page load must not pile up a second event.
    $hooks->ensureInitialIndex();
    sieve_assert(1 === count($GLOBALS['sieve_test']['scheduled']), 'a second admin load does not double-schedule');

    // 3. The backfill is resumable: one batch per tick, cursor carried in an
    //    option. It also has to stay readable the whole way through. The rows of
    //    products it has not reached yet are the previous index, and shoppers are
    //    still filtering against them; emptying the table up front blacks out
    //    filtering and search for the entire rebuild, which on a large catalog is
    //    many ticks, not one request.
    sieve_reset();
    sieve_seed_old_index();
    $hooks->ensureInitialIndex();

    $ticks = 0;
    $blackout = false;
    $lostAhead = [];

    while (false !== wp_next_scheduled($hook) && $ticks < 20) {
        unset($GLOBALS['sieve_test']['scheduled'][$hook]);
        $before = count($GLOBALS['sieve_test']['indexed']);
        $hooks->runBackfill();
        $batch = count($GLOBALS['sieve_test']['indexed']) - $before;
        if ($batch > 3) {
            sieve_assert(false, "tick {$ticks} indexed {$batch} products, over the batch size");
        }
        $ticks++;

        if ([] === $GLOBALS['sieve_test']['rows']) {
            $blackout = true;
        }

        $cursor = sieve_cursor_id();
        foreach (SIEVE_TEST_PRODUCT_IDS as $productId) {
            if ($productId > $cursor && [] === sieve_rows_for($productId)) {
                $lostAhead[] = $productId;
            }
        }
    }

    sieve_assert($expected === $GLOBALS['sieve_test']['indexed'], 'the backfill covers every product exactly once');
    sieve_assert(4 === $ticks, 'the backfill takes one tick per batch plus a closing tick');
    sieve_assert(! $blackout, 'the index is never empty while the rebuild runs');
    sieve_assert(
        [] === $lostAhead,
        'products the rebuild has not reached yet keep their rows (lost: '
            . implode(', ', array_unique($lostAhead)) . ')'
    );
    sieve_assert(
        [] === array_filter(
            $GLOBALS['sieve_test']['queries'],
            static fn (string $sql): bool => str_starts_with($sql, 'TRUNCATE')
        ),
        'the rebuild never empties the table'
    );
    sieve_assert(! sieve_has_old_rows(), 'every stale row is replaced by the time the rebuild finishes');
    sieve_assert(
        [] === sieve_rows_for(SIEVE_TEST_GHOST_ID),
        'rows of a product that no longer exists are dropped at the end'
    );
    sieve_assert(\Sieve\VERSION === get_option('sieve_index_ready'), 'the backfill marks the index ready when it finishes');
    sieve_assert(false === get_option('sieve_index_cursor'), 'the cursor is cleared when the backfill finishes');
    sieve_assert(false === get_transient('sieve_indexing_lock'), 'the lock is released');

    // 4. A cursor written by another version must not be resumed: the products
    //    before it hold rows that version built, and finishing the walk from
    //    there marks the index ready for this version while they stay stale.
    sieve_reset();
    $GLOBALS['sieve_test']['options']['sieve_index_cursor'] = '0.0.1:55';
    $hooks->runBackfill();
    sieve_assert(
        [3, 8, 9] === $GLOBALS['sieve_test']['indexed'],
        'a cursor left by another version is ignored and the walk starts over'
    );

    // A cursor from this version is resumed, which is the point of having one.
    sieve_reset();
    $GLOBALS['sieve_test']['options']['sieve_index_cursor'] = \Sieve\VERSION . ':34';
    $hooks->runBackfill();
    sieve_assert(
        [55, 90] === $GLOBALS['sieve_test']['indexed'],
        'a cursor from this version resumes where the last tick stopped'
    );

    // 5. A tick that finds the lock held (a previous tick fatalled mid-batch and
    //    left it behind) must book a retry, or nothing moves the backfill on.
    sieve_reset();
    set_transient('sieve_indexing_lock', 1, 5 * MINUTE_IN_SECONDS);
    $hooks->runBackfill();

    sieve_assert([] === $GLOBALS['sieve_test']['indexed'], 'a locked tick indexes nothing');
    sieve_assert(false !== wp_next_scheduled($hook), 'a locked tick books a retry instead of dropping the backfill');

    // 6. With wp-cron switched off and no system cron behind it, a scheduled
    //    event never runs. The admin load has to index a batch itself, bounded
    //    and resuming from the cursor, or the index is never built at all.
    sieve_reset();
    define('DISABLE_WP_CRON', true);

    $hooks->ensureInitialIndex();
    sieve_assert(
        [3, 8, 9] === $GLOBALS['sieve_test']['indexed'],
        'with wp-cron off, an admin load indexes one batch inline'
    );

    $hooks->ensureInitialIndex();
    sieve_assert(
        [3, 8, 9, 21, 34, 55] === $GLOBALS['sieve_test']['indexed'],
        'the next admin load carries on from the cursor'
    );

    $loads = 2;
    while ('' === (string) get_option('sieve_index_ready', '') && $loads < 20) {
        $hooks->ensureInitialIndex();
        $loads++;
    }

    sieve_assert($expected === $GLOBALS['sieve_test']['indexed'], 'the inline path indexes every product exactly once');
    sieve_assert(\Sieve\VERSION === get_option('sieve_index_ready'), 'the inline path finishes the build');

    echo $failures > 0 ? "\n{$failures} failure(s)\n" : "\nOK\n";
    exit($failures > 0 ? 1 : 0);
}
