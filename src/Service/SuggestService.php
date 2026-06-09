<?php

declare(strict_types=1);

namespace Sieve\Service;

defined('ABSPATH') || exit;

/**
 * Powers the predictive product search ([sieve_search]). Given a partial term it
 * returns a small, ranked set of matching products with everything the dropdown
 * needs (title, URL, thumbnail, price, SKU). Built on WooCommerce's own product
 * search so it honours catalog visibility and stays correct as the catalog grows.
 */
final class SuggestService
{
    private const MAX_LIMIT = 20;
    private const MAX_CATEGORIES = 4;

    /**
     * @return array{
     *     results: array<int, array{id: int, name: string, url: string, image: string, sku: string, price_html: string}>,
     *     categories: array<int, array{id: int, name: string, url: string, count: int}>,
     *     search_url: string
     * }
     */
    public function suggest(string $term, int $limit = 6, bool $includeOutOfStock = false): array
    {
        $term = trim($term);
        $limit = max(1, min(self::MAX_LIMIT, $limit));

        $empty = ['results' => [], 'categories' => [], 'search_url' => $this->searchUrl($term)];
        if ('' === $term || ! function_exists('wc_get_products')) {
            return $empty;
        }

        // Primary pass: WooCommerce relevance search (titles, content, and SKU
        // when the store enables it), keyed by id so later passes can dedupe.
        $results = $this->productResults($this->query(['s' => $term], $limit, $includeOutOfStock));

        // Fill any shortfall with a partial-SKU pass, so a code like "ABC-12"
        // surfaces its product even when the title search misses it.
        if (count($results) < $limit) {
            foreach ($this->productResults($this->query(['sku' => $term], $limit, $includeOutOfStock)) as $id => $row) {
                if (! isset($results[$id])) {
                    $results[$id] = $row;
                }
                if (count($results) >= $limit) {
                    break;
                }
            }
        }

        return [
            'results' => array_values(array_slice($results, 0, $limit, true)),
            'categories' => $this->matchCategories($term),
            'search_url' => $this->searchUrl($term),
        ];
    }

    /**
     * Run a product query with the shared status / stock / ordering constraints.
     *
     * @param array<string, mixed> $criteria
     * @return array<int, \WC_Product>
     */
    private function query(array $criteria, int $limit, bool $includeOutOfStock): array
    {
        $args = array_merge($criteria, [
            'limit' => $limit,
            'status' => 'publish',
            'orderby' => 'relevance',
            'return' => 'objects',
        ]);
        if (! $includeOutOfStock) {
            $args['stock_status'] = 'instock';
        }

        /** @var array<int, \WC_Product> $products */
        $products = wc_get_products($args);

        return $products;
    }

    /**
     * Map products to the dropdown row shape, keyed by product id for deduping.
     *
     * @param array<int, \WC_Product> $products
     * @return array<int, array{id: int, name: string, url: string, image: string, sku: string, price_html: string}>
     */
    private function productResults(array $products): array
    {
        $results = [];
        foreach ($products as $product) {
            if (! $product instanceof \WC_Product) {
                continue;
            }

            $image = '';
            $thumbId = $product->get_image_id();
            if ($thumbId) {
                $src = wp_get_attachment_image_url((int) $thumbId, 'thumbnail');
                $image = is_string($src) ? $src : '';
            }

            $results[$product->get_id()] = [
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'url' => (string) $product->get_permalink(),
                'image' => $image,
                'sku' => (string) $product->get_sku(),
                'price_html' => (string) $product->get_price_html(),
            ];
        }

        return $results;
    }

    /**
     * Product categories whose name partially matches the term, so a shopper can
     * jump straight to the filtered archive instead of an individual product.
     *
     * @return array<int, array{id: int, name: string, url: string, count: int}>
     */
    private function matchCategories(string $term): array
    {
        if (! taxonomy_exists('product_cat')) {
            return [];
        }

        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'number' => self::MAX_CATEGORIES,
            'name__like' => $term,
            'orderby' => 'count',
            'order' => 'DESC',
        ]);

        if (! is_array($terms)) {
            return [];
        }

        $categories = [];
        foreach ($terms as $cat) {
            if (! $cat instanceof \WP_Term) {
                continue;
            }
            $link = get_term_link($cat);
            $categories[] = [
                'id' => $cat->term_id,
                'name' => $cat->name,
                'url' => is_string($link) ? $link : '',
                'count' => (int) $cat->count,
            ];
        }

        return $categories;
    }

    /**
     * The native WooCommerce search-results URL, for the "view all" link.
     */
    private function searchUrl(string $term): string
    {
        return add_query_arg(
            [
                's' => rawurlencode($term),
                'post_type' => 'product',
            ],
            home_url('/'),
        );
    }
}
