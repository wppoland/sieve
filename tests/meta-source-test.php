<?php

declare(strict_types=1);

/**
 * The "meta:{key}" facet source and the sieve_index_rows filter.
 *
 * FREE only knows the source format: a meta facet must survive the settings
 * round trip with its key intact, may be a range slider, and must be resolved
 * on the numeric column when it is one. The rows themselves come from an
 * add-on through sieve_index_rows. Every other source must behave as before.
 *
 * No framework on purpose: plain PHP with WordPress stubbed.
 *
 *   php tests/meta-source-test.php
 */

namespace {
    define('ABSPATH', __DIR__);
    define('ARRAY_A', 'ARRAY_A');

    $GLOBALS['sieve_test'] = ['rows' => [], 'sql' => [], 'filters' => []];

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

        /** @return array<int, string> */
        public function get_col(string $sql): array
        {
            $GLOBALS['sieve_test']['sql'][] = $sql;
            return ['3'];
        }
    }

    $GLOBALS['wpdb'] = new Sieve_Test_Wpdb();

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
        public function get_name(): string { return 'Tile'; }
    }

    function wc_get_product($id) { return new WC_Product(); }
    function wc_get_attribute_taxonomy_names(): array { return []; }
    function taxonomy_exists(string $taxonomy): bool { return false; }
    function get_the_terms($id, string $taxonomy) { return false; }
    function get_option(string $name, $default = false) { return $GLOBALS['sieve_test']['options'][$name] ?? $default; }
    function update_option(string $name, $value, $autoload = null): bool { $GLOBALS['sieve_test']['options'][$name] = $value; return true; }
    function delete_option(string $name): bool { unset($GLOBALS['sieve_test']['options'][$name]); return true; }
    function wp_count_posts(string $type): object { return (object) ['publish' => 3]; }
    function wp_next_scheduled(string $hook) { return $GLOBALS['sieve_test']['scheduled'][$hook] ?? false; }
    function wp_schedule_single_event(int $ts, string $hook): bool { $GLOBALS['sieve_test']['scheduled'][$hook] = $ts; return true; }
    function add_action(string $hook, callable $cb): void { $GLOBALS['sieve_test']['filters'][$hook][] = $cb; }
    function do_action(string $hook, ...$args): void { foreach ($GLOBALS['sieve_test']['filters'][$hook] ?? [] as $cb) { $cb(...$args); } }
    function get_term_by($field, $value, $taxonomy) { return false; }
    function __($s, $d = null) { return $s; }
    function wc_get_product_visibility_term_ids(): array { return []; }

    function sanitize_key($key): string
    {
        return (string) preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $key));
    }

    function sanitize_text_field($s): string { return trim((string) $s); }
    function esc_attr($s): string { return (string) $s; }
    function esc_html($s): string { return (string) $s; }
    function number_format_i18n($n): string { return (string) $n; }
    function wp_unique_id(string $p = ''): string { return $p . '1'; }

    function add_filter(string $hook, callable $cb): void
    {
        $GLOBALS['sieve_test']['filters'][$hook][] = $cb;
    }

    function apply_filters(string $hook, $value, ...$args)
    {
        foreach ($GLOBALS['sieve_test']['filters'][$hook] ?? [] as $cb) {
            $value = $cb($value, ...$args);
        }
        return $value;
    }
}

namespace Sieve {
    const VERSION = '1.3.0';
}

namespace Sieve\Support {
    final class Normalizer
    {
        public static function tokens(string $s): array { return []; }
        public static function fold(string $s): string { return ''; }
    }
}

namespace {
    require __DIR__ . '/../lib/storefront-kit/Filter/FacetFilterRepository.php';
    require __DIR__ . '/../lib/storefront-kit/Filter/FacetFilterResolver.php';
    require __DIR__ . '/../src/Enum/FacetType.php';
    require __DIR__ . '/../src/Model/Facet.php';
    require __DIR__ . '/../src/Repository/IndexRepository.php';
    require __DIR__ . '/../src/Service/ProductIndexer.php';
    require __DIR__ . '/../src/Service/FilterService.php';
    require __DIR__ . '/../src/Service/UrlService.php';
    require __DIR__ . '/../src/Service/FacetRenderer.php';
    require __DIR__ . '/../src/Contract/HasHooks.php';
    require __DIR__ . '/../src/Hook/IndexerHooks.php';

    use Sieve\Enum\FacetType;
    use Sieve\Model\Facet;

    $failures = [];
    $check = static function (bool $ok, string $what) use (&$failures): void {
        echo ($ok ? 'ok   ' : 'FAIL ') . $what . "\n";
        if (! $ok) {
            $failures[] = $what;
        }
    };

    // Source grammar.
    $meta = Facet::fromArray(['slug' => 'ppm2', 'label' => 'Price per m2', 'type' => 'range_slider', 'source' => 'meta:_Coverage_price<b>']);
    $check('meta:_Coverage_priceb' === $meta->source, 'meta source keeps case and underscores, drops other characters (' . $meta->source . ')');
    $check('_Coverage_priceb' === $meta->metaKey(), 'metaKey() returns the bare key');
    $check('meta:_Coverage_priceb' === $meta->indexKey(), 'index key keeps the prefix, so a meta key named "price" cannot collide');
    $check(FacetType::RangeSlider === $meta->type && $meta->isRange(), 'a meta facet may be a range slider');

    $long = Facet::fromArray(['slug' => 'x', 'source' => 'meta:' . str_repeat('a', 200)]);
    $check(100 === strlen($long->source), 'meta source fits the VARCHAR(100) index column');
    $check('' === Facet::fromArray(['slug' => 'x', 'source' => 'meta:!!'])->source, 'a meta source with no valid key is dropped');

    $metaBox = Facet::fromArray(['slug' => 'ppm2', 'type' => 'checkbox', 'source' => 'meta:_coverage']);
    $check(FacetType::Checkbox === $metaBox->type && ! $metaBox->isRange(), 'a meta facet shown as checkboxes filters on values, not a range');

    // Existing sources unchanged.
    $price = Facet::fromArray(['slug' => 'price', 'type' => 'checkbox', 'source' => 'price']);
    $check(FacetType::RangeSlider === $price->type && $price->isRange(), 'price is still pinned to the range slider');
    $tax = Facet::fromArray(['slug' => 'cat', 'type' => 'range_slider', 'source' => 'tax:Product_Cat']);
    $check(FacetType::Checkbox === $tax->type && ! $tax->isRange() && 'tax:product_cat' === $tax->source, 'a taxonomy range slider still heals to checkboxes');
    $rating = Facet::fromArray(['slug' => 'rating', 'type' => 'range_slider', 'source' => 'rating']);
    $check(! $rating->isRange() && null === $rating->metaKey(), 'rating is not a range and has no meta key');

    // Resolution: range on value_num, checkboxes on value.
    $index = new \Sieve\Repository\IndexRepository();
    $filter = new \Sieve\Service\FilterService($index, new \Sieve\Vendor\StorefrontKit\Filter\FacetFilterResolver());

    $GLOBALS['sieve_test']['sql'] = [];
    $ids = $filter->resolve([$meta], ['ppm2' => '10.5-50']);
    $sql = (string) end($GLOBALS['sieve_test']['sql']);
    $check([3] === $ids, 'a meta range selection resolves to the matching ids');
    $check(str_contains($sql, 'facet_slug = meta:_Coverage_priceb') && str_contains($sql, 'value_num >= 10.5') && str_contains($sql, 'value_num <= 50'), 'a meta range filters the numeric column (' . $sql . ')');

    $GLOBALS['sieve_test']['sql'] = [];
    $filter->resolve([$metaBox], ['ppm2' => '45.5']);
    $sql = (string) end($GLOBALS['sieve_test']['sql']);
    $check(! str_contains($sql, 'value_num'), 'a meta checkbox selection matches stored values');

    // sieve_index_rows: add-on rows are written, default rows untouched.
    (new \Sieve\Service\ProductIndexer($index))->indexProduct(5);
    $baseline = $GLOBALS['sieve_test']['rows'];
    $GLOBALS['sieve_test']['rows'] = [];

    add_filter('sieve_index_rows', static function (array $rows, \WC_Product $product): array {
        $rows[] = ['facet_slug' => 'meta:_coverage', 'value' => '45.5', 'value_num' => 45.5];
        return $rows;
    });
    (new \Sieve\Service\ProductIndexer($index))->indexProduct(5);
    $rows = $GLOBALS['sieve_test']['rows'];

    $check(array_slice($rows, 0, count($baseline)) === $baseline, 'the built-in rows are unchanged when the filter adds rows');
    $last = end($rows);
    $check(is_array($last) && 'meta:_coverage' === $last['facet_slug'] && 45.5 === $last['value_num'], 'a row added through sieve_index_rows is written, also for a product hidden from search');

    $GLOBALS['sieve_test']['filters']['sieve_index_rows'] = [static fn () => 'junk'];
    $GLOBALS['sieve_test']['rows'] = [];
    (new \Sieve\Service\ProductIndexer($index))->indexProduct(5);
    $check($GLOBALS['sieve_test']['rows'] === $baseline, 'a filter returning a non-array falls back to the built-in rows');

    // Numeric values as options: PHP stores the "10" key as an int.
    $html = (new \Sieve\Service\FacetRenderer())->render($metaBox, ['10' => 2, '12.50' => 1], ['10']);
    $check(str_contains($html, 'value="10" checked') && str_contains($html, 'value="12.50"'), 'a facet with integer-like values renders and keeps the selection checked');

    $metaSearch = Facet::fromArray(['slug' => 'x', 'type' => 'search', 'source' => 'meta:_coverage']);
    $check(FacetType::Checkbox === $metaSearch->type, 'a meta facet cannot be a search box (heals to checkboxes)');

    // Value labels: meta options sort naturally and an add-on may label them.
    $renderer = new \Sieve\Service\FacetRenderer();
    $html = $renderer->render($metaBox, ['12' => 1, '8' => 1, '10' => 1], []);
    $check(strpos($html, 'value="8"') < strpos($html, 'value="10"') && strpos($html, 'value="10"') < strpos($html, 'value="12"'), 'meta options sort as numbers: 8, 10, 12');
    $priceLabelBefore = $renderer->valueLabel($price, '10-50');
    add_filter('sieve_meta_value_label', static fn (string $label, Facet $facet, string $value): string => $label . ' zl/m2');
    $check('45.50 zl/m2' === $renderer->valueLabel($metaBox, '45.50'), 'sieve_meta_value_label labels a meta value');
    $check(str_contains($renderer->render($metaBox, ['45.50' => 1], []), '>45.50 zl/m2<'), 'the option label carries the add-on label');
    $check('10-50' === $priceLabelBefore && '10-50' === $renderer->valueLabel($price, '10-50'), 'price labels ignore the meta label filter');

    // Rebuild API for add-ons.
    $hooks = new \Sieve\Hook\IndexerHooks(new \Sieve\Service\ProductIndexer($index));
    $hooks->registerHooks();
    $GLOBALS['sieve_test']['options'] = ['sieve_index_ready' => '9.9.9', 'sieve_index_cursor' => '9.9.9:42'];
    do_action('sieve_request_reindex');
    $opts = $GLOBALS['sieve_test']['options'];
    $check('0' === ($opts['sieve_index_ready'] ?? null) && ! isset($opts['sieve_index_cursor']), 'sieve_request_reindex resets the index version and drops a half-done cursor');
    $check(isset($GLOBALS['sieve_test']['scheduled']['sieve_index_backfill']), 'sieve_request_reindex schedules the background rebuild');

    if ([] !== $failures) {
        fwrite(STDERR, count($failures) . " failure(s)\n");
        exit(1);
    }

    echo "all passed\n";
}
