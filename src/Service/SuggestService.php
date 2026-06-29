<?php

declare(strict_types=1);

namespace Sieve\Service;

defined('ABSPATH') || exit;

use Sieve\Support\Normalizer;

/**
 * Powers the predictive product search ([sieve_search]) and the in-grid search
 * combobox. Given a partial term it returns a small, ranked set of matching
 * products with everything the dropdown needs (title, URL, thumbnail, price, SKU).
 *
 * Matching is diacritic-insensitive and typo-tolerant: term -> id resolution is
 * delegated to the shared SearchResolver (a folded '_search' token index plus a
 * bounded Levenshtein fuzzy tier, so "lozko" finds "łóżko"). When NOT scoped, an
 * empty index hit falls back to WooCommerce's own relevance search so
 * description/content matches stay reachable and the post-upgrade reindex window
 * is covered. When scoped to a facet selection, no WC fallback runs (out-of-scope
 * results would mislead).
 */
final class SuggestService
{
    private const MAX_LIMIT = 20;
    private const MAX_CATEGORIES = 4;
    // How many ordered ids to hydrate: a window wider than $limit so that, when
    // the top matches are filtered out by the stock constraint, lower-ranked
    // index-tier matches still fill the dropdown (rather than ceding to the WC
    // fallback). Trimmed back to $limit after hydration + ordering.
    private const HYDRATE_CAP = 50;

    public function __construct(
        private readonly SearchResolver $resolver,
    ) {
    }

    /**
     * @param array<int, int>|null $constrainIds When non-null, suggestions are
     *        intersected with this id set (the active grid scope): categories and
     *        the WC fallback are skipped so an out-of-scope match never appears.
     * @return array{
     *     results: array<int, array{id: int, name: string, url: string, image: string, sku: string, price_html: string}>,
     *     categories: array<int, array{id: int, name: string, url: string, count: int, count_label: string}>,
     *     search_url: string
     * }
     */
    public function suggest(string $term, int $limit = 6, bool $includeOutOfStock = false, ?array $constrainIds = null): array
    {
        $term = trim($term);
        $limit = max(1, min(self::MAX_LIMIT, $limit));
        $scoped = null !== $constrainIds;

        $empty = ['results' => [], 'categories' => [], 'search_url' => $this->searchUrl($term)];
        if ('' === $term || ! function_exists('wc_get_products')) {
            return $empty;
        }

        // Resolve the term to ids via the shared resolver (same path the grid
        // uses, so the dropdown and the grid can never disagree). null => the
        // term was untokenizable; treat as no index hit.
        $orderedIds = $this->resolver->resolve($term) ?? [];

        // Scoped: intersect with the active grid id set ("shoes within red").
        if ($scoped) {
            $orderedIds = array_values(array_intersect($orderedIds, $constrainIds));
        }

        // Hydrate the index-tier ids in order. The index can hold a now-out-of-
        // stock product between reindexes, so the stock_status constraint must
        // stay here even though the index itself is not stock-aware.
        $results = [];
        if ([] !== $orderedIds) {
            // Hydrate a wider window than $limit so the stock filter dropping a
            // top match does not waste a slot; orderByIds + the final slice trim
            // back to $limit after stock filtering.
            $results = $this->productResults(
                $this->query(['include' => array_slice($orderedIds, 0, self::HYDRATE_CAP)], self::HYDRATE_CAP, $includeOutOfStock)
            );
            $results = $this->orderByIds($results, $orderedIds);
        }

        // FINAL FALLBACK: only when unscoped AND the index tier found NOTHING.
        // Keeping the fast index path authoritative for the common case is the
        // Web-Vitals win (no per-keystroke WP_Query); when the index has no hit
        // at all we fall back to WooCommerce's own search so description/content
        // matches stay reachable and the post-upgrade reindex window is covered.
        // A scoped search skips this: out-of-scope WC results would mislead.
        if (! $scoped && [] === $results) {
            $results = $this->mergeShortfall($results, $this->query(['s' => $term], $limit, $includeOutOfStock), $limit);
        }
        if (! $scoped && [] === $results) {
            $results = $this->mergeShortfall($results, $this->query(['sku' => $term], $limit, $includeOutOfStock), $limit);
        }

        return [
            'results' => array_values(array_slice($results, 0, $limit, true)),
            // Categories are facets in the in-grid combobox, so a scoped search
            // never returns them; the standalone widget keeps them.
            'categories' => $scoped ? [] : $this->matchCategories($term),
            'search_url' => $this->searchUrl($term),
        ];
    }

    /**
     * Order a id-keyed result map by an explicit id sequence, dropping any ids
     * that did not hydrate (e.g. filtered out by the stock_status constraint).
     *
     * @param array<int, array{id: int, name: string, url: string, image: string, sku: string, price_html: string}> $results
     * @param array<int, int> $orderedIds
     * @return array<int, array{id: int, name: string, url: string, image: string, sku: string, price_html: string}>
     */
    private function orderByIds(array $results, array $orderedIds): array
    {
        $ordered = [];
        foreach ($orderedIds as $id) {
            if (isset($results[$id])) {
                $ordered[$id] = $results[$id];
            }
        }
        return $ordered;
    }

    /**
     * Merge a WooCommerce fallback pass into the result map, deduping by id and
     * keeping the existing (index-tier) order ahead of the new rows.
     *
     * @param array<int, array{id: int, name: string, url: string, image: string, sku: string, price_html: string}> $results
     * @param array<int, \WC_Product> $products
     * @return array<int, array{id: int, name: string, url: string, image: string, sku: string, price_html: string}>
     */
    private function mergeShortfall(array $results, array $products, int $limit): array
    {
        foreach ($this->productResults($products) as $id => $row) {
            if (! isset($results[$id])) {
                $results[$id] = $row;
            }
            if (count($results) >= $limit) {
                break;
            }
        }
        return $results;
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
            'return' => 'objects',
            // Never surface a product the store excluded from search results,
            // whichever tier (index hydration or WC fallback) produced the ids.
            'visibility' => 'search',
        ]);
        // Relevance ordering is only generated for keyword ('s') searches; for
        // the SKU pass WooCommerce produces no relevance SQL, so request it only
        // when a search term is present and let WC use its default otherwise.
        if (isset($criteria['s'])) {
            $args['orderby'] = 'relevance';
        }
        // For the index-tier hydration, preserve our supplied id order so WC
        // returns rows in the same sequence (we also re-order in PHP afterward).
        if (isset($criteria['include'])) {
            $args['orderby'] = 'post__in';
        }
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
     * @return array<int, array{id: int, name: string, url: string, count: int, count_label: string}>
     */
    private function matchCategories(string $term): array
    {
        if (! taxonomy_exists('product_cat')) {
            return [];
        }

        $foldedTerm = Normalizer::fold($term);
        if ('' === $foldedTerm) {
            return [];
        }

        // Fetch the busiest categories and match in PHP on the folded name, so a
        // diacritic-insensitive query still finds them (get_terms' name__like is
        // collation-bound and cannot fold Polish diacritics). Bounded to the 200
        // most-populated categories; stores with more are rare and degrade
        // gracefully (the long tail of tiny categories is simply not searched).
        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'number' => 200,
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
            if (! str_contains(Normalizer::fold($cat->name), $foldedTerm)) {
                continue;
            }
            // Skip a category whose permalink cannot be resolved: it would
            // render as a dead, selectable suggestion linking nowhere.
            $link = get_term_link($cat);
            if (! is_string($link) || '' === $link) {
                continue;
            }
            $count = (int) $cat->count;
            $categories[] = [
                'id' => $cat->term_id,
                'name' => $cat->name,
                'url' => $link,
                'count' => $count,
                // Pluralised server-side so every locale's plural rules apply,
                // rather than filling a single "%d products" template in JS.
                'count_label' => sprintf(
                    /* translators: %d: number of products in a category. */
                    _n('%d product', '%d products', $count, 'sieve'),
                    $count,
                ),
            ];
            if (count($categories) >= self::MAX_CATEGORIES) {
                break;
            }
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
