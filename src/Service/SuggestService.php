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

    /**
     * @return array{
     *     results: array<int, array{id: int, name: string, url: string, image: string, sku: string, price_html: string}>,
     *     search_url: string
     * }
     */
    public function suggest(string $term, int $limit = 6, bool $includeOutOfStock = false): array
    {
        $term = trim($term);
        $limit = max(1, min(self::MAX_LIMIT, $limit));

        $empty = ['results' => [], 'search_url' => $this->searchUrl($term)];
        if ('' === $term || ! function_exists('wc_get_products')) {
            return $empty;
        }

        $args = [
            's' => $term,
            'limit' => $limit,
            'status' => 'publish',
            'orderby' => 'relevance',
            'return' => 'objects',
        ];
        if (! $includeOutOfStock) {
            $args['stock_status'] = 'instock';
        }

        /** @var array<int, \WC_Product> $products */
        $products = wc_get_products($args);

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

            $results[] = [
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'url' => (string) $product->get_permalink(),
                'image' => $image,
                'sku' => (string) $product->get_sku(),
                'price_html' => (string) $product->get_price_html(),
            ];
        }

        return [
            'results' => $results,
            'search_url' => $this->searchUrl($term),
        ];
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
