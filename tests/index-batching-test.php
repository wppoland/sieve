<?php

declare(strict_types=1);

/**
 * Fails if the catalog walk stops being batched.
 *
 * Sieve used to index the whole catalog inside admin_init, which made every
 * admin page load pay for it. This asserts the two halves of the fix: the
 * indexer reads products in bounded, filterable batches with a keyset cursor,
 * and admin_init only schedules the backfill instead of running it.
 *
 * No framework on purpose: plain PHP with WordPress stubbed.
 *
 *   php tests/index-batching-test.php
 */

namespace {
    define('ABSPATH', __DIR__);
    define('MINUTE_IN_SECONDS', 60);

    const SIEVE_TEST_PRODUCT_IDS = [3, 8, 9, 21, 34, 55, 90];

    $GLOBALS['sieve_test'] = [
        'selects' => [],
        'indexed' => [],
        'rows' => [],
        'options' => [],
        'transients' => [],
        'scheduled' => [],
        'filters' => [],
    ];

    final class Sieve_Test_Wpdb
    {
        public string $prefix = 'wp_';
        public string $posts = 'wp_posts';

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
            return 1;
        }

        public function query(string $sql): int
        {
            if (str_starts_with($sql, 'TRUNCATE')) {
                $GLOBALS['sieve_test']['rows'] = [];
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
}

namespace Sieve {
    const VERSION = '1.1.6';
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
        $GLOBALS['sieve_test']['indexed'] = [];
        $GLOBALS['sieve_test']['rows'] = [];
        $GLOBALS['sieve_test']['options'] = [];
        $GLOBALS['sieve_test']['transients'] = [];
        $GLOBALS['sieve_test']['scheduled'] = [];
    }

    // Batch size is a filter, not a setting screen.
    $GLOBALS['sieve_test']['filters']['sieve_index_batch_size'] = static fn (): int => 3;

    $indexer = new \Sieve\Service\ProductIndexer(new \Sieve\Repository\IndexRepository());
    $hooks = new \Sieve\Hook\IndexerHooks($indexer);

    // 1. indexAll walks the catalog in bounded batches, every product once, in ID order.
    sieve_reset();
    $count = $indexer->indexAll();
    $expected = SIEVE_TEST_PRODUCT_IDS;

    sieve_assert(7 === $count, 'indexAll reports every product');
    sieve_assert($expected === $GLOBALS['sieve_test']['indexed'], 'every product indexed once, in ID order');
    sieve_assert(4 === count($GLOBALS['sieve_test']['selects']), 'seven products at batch size three take four queries');

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
    sieve_assert(
        false !== wp_next_scheduled(\Sieve\Hook\IndexerHooks::BACKFILL_HOOK),
        'admin_init schedules the backfill'
    );

    // A second admin page load must not pile up a second event.
    $hooks->ensureInitialIndex();
    sieve_assert(1 === count($GLOBALS['sieve_test']['scheduled']), 'a second admin load does not double-schedule');

    // 3. The backfill is resumable: one batch per tick, cursor carried in an option.
    sieve_reset();
    $hooks->ensureInitialIndex();

    $ticks = 0;
    while (false !== wp_next_scheduled(\Sieve\Hook\IndexerHooks::BACKFILL_HOOK) && $ticks < 20) {
        unset($GLOBALS['sieve_test']['scheduled'][\Sieve\Hook\IndexerHooks::BACKFILL_HOOK]);
        $before = count($GLOBALS['sieve_test']['indexed']);
        $hooks->runBackfill();
        $batch = count($GLOBALS['sieve_test']['indexed']) - $before;
        if ($batch > 3) {
            sieve_assert(false, "tick {$ticks} indexed {$batch} products, over the batch size");
        }
        $ticks++;
    }

    sieve_assert($expected === $GLOBALS['sieve_test']['indexed'], 'the backfill covers every product exactly once');
    sieve_assert(4 === $ticks, 'the backfill takes one tick per batch plus a closing tick');
    sieve_assert('1.1.6' === get_option('sieve_index_ready'), 'the backfill marks the index ready when it finishes');
    sieve_assert(false === get_option('sieve_index_cursor'), 'the cursor is cleared when the backfill finishes');
    sieve_assert(false === get_transient('sieve_indexing_lock'), 'the lock is released');

    echo $failures > 0 ? "\n{$failures} failure(s)\n" : "\nOK\n";
    exit($failures > 0 ? 1 : 0);
}
