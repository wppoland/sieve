<?php

declare(strict_types=1);

/**
 * Fails if a facet count can promise products the grid does not show.
 *
 * Two shapes, both found on a live store in the 2026-10-02 review:
 *
 * 1. A product is indexed only under the category it is assigned to. A tree
 *    facet then shows the parent (Clothing above Shirts) with no count, and
 *    ticking it returns no products.
 * 2. Counts and price bounds read every indexed product, while the grid takes
 *    WooCommerce's catalogue tax query. A product excluded from the catalogue,
 *    or out of stock with "Hide out of stock items" on, was counted and never
 *    shown.
 *
 * No framework on purpose: plain PHP with WordPress stubbed.
 *
 *   php tests/catalog-parity-test.php
 */

namespace {
    define('ABSPATH', __DIR__);
    define('ARRAY_A', 'ARRAY_A');

    const SIEVE_TEST_EXCLUDE_FROM_CATALOG = 7;
    const SIEVE_TEST_OUTOFSTOCK = 9;

    $GLOBALS['sieve_test'] = ['rows' => [], 'sql' => [], 'hide_oos' => 'no'];

    final class Sieve_Test_Wpdb
    {
        public string $prefix = 'wp_';
        public string $posts = 'wp_posts';
        public string $term_relationships = 'wp_term_relationships';

        /** @param array<int, mixed>|mixed $args */
        public function prepare(string $query, $args = null, ...$rest): string
        {
            $values = is_array($args) ? $args : array_merge([$args], $rest);

            return (string) preg_replace_callback(
                '/%[dsf]/',
                static function () use (&$values): string {
                    return (string) array_shift($values);
                },
                $query
            );
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
            return 0;
        }

        /** @return array<int, array<string, mixed>> */
        public function get_results(string $sql, $output = null): array
        {
            $GLOBALS['sieve_test']['sql'][] = $sql;
            return [];
        }

        /** @return array<string, mixed> */
        public function get_row(string $sql, $output = null): array
        {
            $GLOBALS['sieve_test']['sql'][] = $sql;
            return ['lo' => 1, 'hi' => 2];
        }
    }

    $GLOBALS['wpdb'] = new Sieve_Test_Wpdb();

    final class WP_Term
    {
        public function __construct(public int $term_id, public string $slug, public int $parent)
        {
        }
    }

    // Clothing (10) > Shirts (11) > Tees (12); Accessories (20) has no parent.
    $GLOBALS['sieve_test_terms'] = [
        10 => new WP_Term(10, 'clothing', 0),
        11 => new WP_Term(11, 'shirts', 10),
        12 => new WP_Term(12, 'tees', 11),
        20 => new WP_Term(20, 'accessories', 0),
    ];

    class WC_Product
    {
        public function get_id(): int { return 5; }
        public function get_status(): string { return 'publish'; }
        public function get_price(): string { return '10'; }
        public function get_stock_status(): string { return 'instock'; }
        public function is_on_sale(): bool { return false; }
        public function get_average_rating(): string { return '0'; }
        public function get_catalog_visibility(): string { return 'hidden'; }
        public function get_sku(): string { return ''; }
        public function get_name(): string { return 'Tee'; }
    }

    function wc_get_product($id) { return new WC_Product(); }
    function wc_get_attribute_taxonomy_names(): array { return []; }
    function is_taxonomy_hierarchical(string $taxonomy): bool { return 'product_cat' === $taxonomy; }

    function get_the_terms($id, string $taxonomy)
    {
        $t = $GLOBALS['sieve_test_terms'];
        // Assigned to the grandchild and, redundantly, to its own parent.
        return 'product_cat' === $taxonomy ? [$t[12], $t[11], $t[20]] : false;
    }

    function get_ancestors(int $id, string $taxonomy, string $type = ''): array
    {
        $out = [];
        $term = $GLOBALS['sieve_test_terms'][$id] ?? null;
        while ($term && $term->parent) {
            $out[] = $term->parent;
            $term = $GLOBALS['sieve_test_terms'][$term->parent] ?? null;
        }
        return $out;
    }

    function get_term(int $id, string $taxonomy) { return $GLOBALS['sieve_test_terms'][$id] ?? null; }

    function wc_get_product_visibility_term_ids(): array
    {
        return [
            'exclude-from-catalog' => SIEVE_TEST_EXCLUDE_FROM_CATALOG,
            'exclude-from-search' => 8,
            'outofstock' => SIEVE_TEST_OUTOFSTOCK,
        ];
    }

    function get_option(string $name, $default = false)
    {
        return 'woocommerce_hide_out_of_stock_items' === $name ? $GLOBALS['sieve_test']['hide_oos'] : $default;
    }

    function apply_filters(string $hook, $value) { return $value; }
}

namespace Sieve\Support {
    final class Normalizer
    {
        public static function tokens(string $s): array { return []; }
        public static function fold(string $s): string { return ''; }
    }
}

namespace {
    // The facet-filter contract IndexRepository implements lives in the
    // bundled storefront kit; only the interface is needed here.
    require __DIR__ . '/../lib/storefront-kit/Filter/FacetFilterRepository.php';
    require __DIR__ . '/../src/Repository/IndexRepository.php';
    require __DIR__ . '/../src/Service/ProductIndexer.php';

    $failures = [];
    $check = static function (bool $ok, string $what) use (&$failures): void {
        echo ($ok ? 'ok   ' : 'FAIL ') . $what . "\n";
        if (! $ok) {
            $failures[] = $what;
        }
    };

    $index = new \Sieve\Repository\IndexRepository();
    (new \Sieve\Service\ProductIndexer($index))->indexProduct(5);

    $cats = array_map(
        static fn (array $r): string => (string) $r['value'],
        array_values(array_filter($GLOBALS['sieve_test']['rows'], static fn (array $r): bool => 'product_cat' === $r['facet_slug']))
    );
    sort($cats);

    $check(in_array('clothing', $cats, true), 'a product in a grandchild category is indexed under the top-level parent');
    $check(in_array('shirts', $cats, true), 'and under the intermediate parent');
    $check($cats === ['accessories', 'clothing', 'shirts', 'tees'], 'each category is indexed once, even when assigned twice (' . implode(',', $cats) . ')');

    $index->valueCounts('product_cat');
    $sql = (string) end($GLOBALS['sieve_test']['sql']);
    $check(str_contains($sql, 'NOT IN (SELECT object_id FROM wp_term_relationships WHERE term_taxonomy_id IN (' . SIEVE_TEST_EXCLUDE_FROM_CATALOG . '))'), 'counts leave out products excluded from the catalogue');

    $GLOBALS['sieve_test']['hide_oos'] = 'yes';
    $index->valueCounts('product_cat', [3, 4]);
    $sql = (string) end($GLOBALS['sieve_test']['sql']);
    $check(str_contains($sql, 'term_taxonomy_id IN (' . SIEVE_TEST_EXCLUDE_FROM_CATALOG . ', ' . SIEVE_TEST_OUTOFSTOCK . ')'), 'with "Hide out of stock items" on, counts leave out-of-stock products out too');
    $check(str_contains($sql, 'AND object_id IN (3, 4)'), 'the dependent-count restriction still applies after the exclusion');

    $index->numericBounds('price');
    $sql = (string) end($GLOBALS['sieve_test']['sql']);
    $check(str_contains($sql, 'NOT IN (SELECT object_id FROM wp_term_relationships'), 'price slider bounds leave hidden products out');

    if ([] !== $failures) {
        fwrite(STDERR, count($failures) . " failure(s)\n");
        exit(1);
    }

    echo "all passed\n";
}
