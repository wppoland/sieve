<?php
/**
 * Idempotent seed for the Playwright e2e suite. Ensures a filter page ([sieve])
 * and a search page ([sieve_search]) exist, ensures the Sieve index is built,
 * and prints a JSON map of the URLs the tests need. Run via:
 *   wp eval-file wp-content/plugins/sieve/scripts/seed-e2e.php
 *
 * @package Sieve
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Ensure a page with the given slug + content exists; return its permalink.
 */
$ensure_page = static function (string $slug, string $title, string $content): string {
    $existing = get_page_by_path($slug);
    if ($existing instanceof WP_Post) {
        wp_update_post(['ID' => $existing->ID, 'post_content' => $content, 'post_status' => 'publish']);
        return (string) get_permalink($existing->ID);
    }
    $id = wp_insert_post([
        'post_title' => $title,
        'post_name' => $slug,
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_content' => $content,
    ]);
    return (string) get_permalink((int) $id);
};

$filterUrl = $ensure_page('sieve-e2e-filter', 'Sieve E2E Filter', '[sieve]');
$searchUrl = $ensure_page('sieve-e2e-search', 'Sieve E2E Search', '[sieve_search placeholder="Search products" limit="6"]');

// Make sure the index is populated so the filter page has results.
if (class_exists('Sieve\\Plugin')) {
    $indexer = Sieve\Plugin::instance()->container()->get(Sieve\Service\ProductIndexer::class);
    $indexer->indexAll();
}

// A product title fragment that should yield search results (Woo sample data).
$term = 'hood';
$products = function_exists('wc_get_products')
    ? wc_get_products(['s' => $term, 'limit' => 1, 'status' => 'publish'])
    : [];
$hasResults = ! empty($products);

echo wp_json_encode([
    'filterUrl' => $filterUrl,
    'searchUrl' => $searchUrl,
    'searchTerm' => $term,
    'searchHasResults' => $hasResults,
]) . "\n";
