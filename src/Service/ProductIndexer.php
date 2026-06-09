<?php

declare(strict_types=1);

namespace Sieve\Service;

defined('ABSPATH') || exit;

use Sieve\Repository\IndexRepository;

/**
 * Builds the Sieve index from WooCommerce products.
 *
 * Every product is fully indexed across all known sources (taxonomies, global
 * attributes, price, stock, on-sale, rating) regardless of which facets are
 * currently configured. That makes adding or removing a facet a zero-cost
 * settings change with no re-index required.
 */
final class ProductIndexer
{
    public function __construct(private readonly IndexRepository $index)
    {
    }

    /**
     * Re-index a single product by ID.
     */
    public function indexProduct(int $productId): void
    {
        $product = function_exists('wc_get_product') ? wc_get_product($productId) : null;

        if (! $product instanceof \WC_Product || 'publish' !== $product->get_status()) {
            $this->index->deleteObject($productId);
            return;
        }

        $this->index->reindexObject($productId, $this->rowsForProduct($product));
    }

    public function removeProduct(int $productId): void
    {
        $this->index->deleteObject($productId);
    }

    /**
     * Re-index the whole catalog. Returns the number of products indexed.
     * Synchronous; suitable for the catalog sizes this MVP targets. Large-catalog
     * background batching is a post-MVP concern.
     */
    public function indexAll(): int
    {
        $this->index->truncate();

        $ids = get_posts([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
            'suppress_filters' => true,
        ]);

        $count = 0;
        foreach ($ids as $id) {
            $this->indexProduct((int) $id);
            $count++;
        }

        return $count;
    }

    /**
     * @return array<int, array{facet_slug: string, value: string, value_num: float|null}>
     */
    private function rowsForProduct(\WC_Product $product): array
    {
        $rows = [];
        $productId = $product->get_id();

        // Taxonomies: categories, tags, and every global attribute (pa_*).
        $taxonomies = ['product_cat', 'product_tag'];
        if (function_exists('wc_get_attribute_taxonomy_names')) {
            $taxonomies = array_merge($taxonomies, wc_get_attribute_taxonomy_names());
        }

        foreach ($taxonomies as $taxonomy) {
            $terms = get_the_terms($productId, $taxonomy);
            if (is_array($terms)) {
                foreach ($terms as $term) {
                    $rows[] = [
                        'facet_slug' => $taxonomy,
                        'value' => $term->slug,
                        'value_num' => null,
                    ];
                }
            }
        }

        // Price (use the active display price).
        $price = $product->get_price();
        if ('' !== $price && null !== $price) {
            $rows[] = [
                'facet_slug' => 'price',
                'value' => '',
                'value_num' => (float) $price,
            ];
        }

        // Stock status.
        $rows[] = [
            'facet_slug' => 'stock',
            'value' => (string) $product->get_stock_status(),
            'value_num' => null,
        ];

        // On sale.
        if ($product->is_on_sale()) {
            $rows[] = [
                'facet_slug' => 'on_sale',
                'value' => 'yes',
                'value_num' => null,
            ];
        }

        // Average rating (rounded to whole stars).
        $rating = (float) $product->get_average_rating();
        if ($rating > 0) {
            $rounded = (string) (int) round($rating);
            $rows[] = [
                'facet_slug' => 'rating',
                'value' => $rounded,
                'value_num' => (float) $rounded,
            ];
        }

        return $rows;
    }
}
