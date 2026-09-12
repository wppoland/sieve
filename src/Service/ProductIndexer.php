<?php

declare(strict_types=1);

namespace Sieve\Service;

defined('ABSPATH') || exit;

use Sieve\Repository\IndexRepository;
use Sieve\Support\Normalizer;

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
     * Products read (and held in memory) per batch. Filter
     * 'sieve_index_batch_size' to change it on a slow or memory-tight host.
     */
    public const BATCH_SIZE = 200;

    public static function batchSize(): int
    {
        $size = (int) apply_filters('sieve_index_batch_size', self::BATCH_SIZE);

        return $size > 0 ? $size : self::BATCH_SIZE;
    }

    /**
     * Drop rows left behind by products that are no longer published. Run at the
     * end of a full walk, which has by then replaced the rows of every product
     * that still exists.
     */
    public function removeOrphans(): int
    {
        return $this->index->deleteOrphans();
    }

    /**
     * Index one batch of published products whose ID is greater than $afterId,
     * in ascending ID order. Returns the IDs indexed, empty once the catalog is
     * exhausted. A keyset cursor, not OFFSET, so a product saved mid-run cannot
     * make the walk skip or repeat a row.
     *
     * @return array<int, int>
     */
    public function indexBatch(int $afterId, ?int $limit = null): array
    {
        global $wpdb;

        $limit = $limit ?? self::batchSize();

        $ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish' AND ID > %d ORDER BY ID ASC LIMIT %d",
                $afterId,
                $limit,
            )
        );

        $ids = array_map('intval', (array) $ids);
        foreach ($ids as $id) {
            $this->indexProduct($id);
        }

        return $ids;
    }

    /**
     * Re-index the whole catalog in one request and return the number of
     * products indexed. This is the manual rebuild behind the settings screen
     * button, and the way to build the index on a site where cron never runs.
     *
     * Each product's rows are replaced in place, so filtering keeps working
     * throughout; rows belonging to products that are gone are dropped at the
     * end. The background backfill runs the same batches across cron ticks
     * instead (see IndexerHooks).
     */
    public function indexAll(): int
    {
        $count = 0;
        $cursor = 0;

        while (true) {
            $ids = $this->indexBatch($cursor);
            if ([] === $ids) {
                $this->removeOrphans();

                return $count;
            }

            $count += count($ids);
            $cursor = (int) end($ids);

            $this->forgetBatch();
        }
    }

    /**
     * Drop the per-request object cache between batches.
     *
     * Indexing a product pulls its WC_Product, its postmeta and its terms into
     * that cache, and nothing evicts them inside a single request, so without
     * this the peak memory of a manual rebuild still grows with the catalog no
     * matter how small the batches are.
     *
     * Only the in-process cache is cleared. On a site running a persistent
     * object cache drop-in this does nothing: core's fallback for a drop-in
     * that has no runtime flush of its own is to flush the whole shared cache,
     * which is not Sieve's to throw away.
     */
    private function forgetBatch(): void
    {
        global $wpdb;

        $wpdb->queries = [];

        if (! wp_using_ext_object_cache() && function_exists('wp_cache_flush_runtime')) {
            wp_cache_flush_runtime();
        }
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

        // Predictive-search token index. '_search' is a reserved internal
        // facet_slug holding diacritic-folded tokens from the product name and
        // SKU, so a query folded the same way can match across diacritics and
        // small typos. Each token is stored as its own row.
        //
        // Only index tokens for products WooCommerce would surface in its own
        // search (visibility 'visible' or 'search'); skipping hidden and
        // catalog-only products keeps the predictive dropdown from exposing
        // search-excluded products and trims index size.
        if (! in_array($product->get_catalog_visibility(), ['visible', 'search'], true)) {
            return $rows;
        }

        $sku = (string) $product->get_sku();
        $tokens = array_merge(
            Normalizer::tokens($product->get_name()),
            Normalizer::tokens($sku),
        );
        // Also store the full folded SKU as one token so a code like "abc-12"
        // (which folds to "abc 12") remains findable as a single unit.
        $foldedSku = Normalizer::fold($sku);
        if ('' !== $foldedSku) {
            $tokens[] = $foldedSku;
        }

        foreach (array_unique($tokens) as $token) {
            $rows[] = [
                'facet_slug' => '_search',
                'value' => mb_substr($token, 0, 191, 'UTF-8'),
                'value_num' => null,
            ];
        }

        return $rows;
    }
}
