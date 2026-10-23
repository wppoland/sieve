<?php

declare(strict_types=1);

namespace Sieve\Service;

defined('ABSPATH') || exit;

use Sieve\Model\Facet;
use Sieve\Repository\IndexRepository;

/**
 * Computes dependent facet counts: each value's count reflects the other active
 * filters, so the numbers stay truthful as shoppers narrow down. Memoised per
 * request to avoid recomputing the candidate set for every value.
 */
final class FacetCountService
{
    /** @var array<string, array<string, int>> */
    private array $memo = [];

    public function __construct(
        private readonly IndexRepository $index,
        private readonly FilterService $filter,
    ) {
    }

    /**
     * Precompute and memoize counts for multiple facets in batched queries.
     *
     * Facets sharing identical candidate object sets are grouped and fetched
     * in a single SQL query, reducing N sequential queries to 1 (or 1 per candidate group).
     *
     * @param array<int, Facet> $facets
     * @param array<string, string> $filters
     * @param array<int, int>|null $searchIds
     * @return array<string, array<string, int>> [facet_slug => [value => count]]
     */
    public function batchCounts(array $facets, array $filters, ?array $searchIds = null): array
    {
        $results = [];
        $groups = [];

        foreach ($facets as $facet) {
            if ($facet->isSearch() || $facet->isPrice()) {
                continue;
            }

            $cacheKey = $this->cacheKey($facet->slug, $filters, $searchIds);
            if (isset($this->memo[$cacheKey])) {
                $results[$facet->slug] = $this->memo[$cacheKey];
                continue;
            }

            $candidates = $this->filter->resolve($facets, $filters, $facet->slug, $searchIds);

            if (is_array($candidates)) {
                if ([] === $candidates) {
                    $this->memo[$cacheKey] = [];
                    $results[$facet->slug] = [];
                    continue;
                }
                $sorted = $candidates;
                sort($sorted);
                $groupKey = md5(implode(',', $sorted));
            } else {
                $groupKey = '__all__';
            }

            if (! isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'candidates' => $candidates,
                    'facets' => [],
                ];
            }

            $groups[$groupKey]['facets'][] = $facet;
        }

        foreach ($groups as $group) {
            /** @var array<int, Facet> $groupFacets */
            $groupFacets = $group['facets'];
            /** @var array<int, int>|null $candidates */
            $candidates = $group['candidates'];

            $slugs = array_values(array_unique(array_map(
                static fn (Facet $f): string => $f->indexKey(),
                $groupFacets
            )));

            $countsBySlug = $this->index->batchedValueCounts($slugs, $candidates);

            foreach ($groupFacets as $facet) {
                $facetCounts = $countsBySlug[$facet->indexKey()] ?? [];
                $cacheKey = $this->cacheKey($facet->slug, $filters, $searchIds);
                $this->memo[$cacheKey] = $facetCounts;
                $results[$facet->slug] = $facetCounts;
            }
        }

        return $results;
    }

    /**
     * Value => count for a facet, given the full active selection (the facet's
     * own selection is excluded so its options never collapse to one).
     *
     * @param array<int, Facet> $facets
     * @param array<string, string> $filters
     * @param array<int, int>|null $searchIds Resolved search ids (search-aware counts).
     * @return array<string, int>
     */
    public function countsFor(Facet $facet, array $facets, array $filters, ?array $searchIds = null): array
    {
        $cacheKey = $this->cacheKey($facet->slug, $filters, $searchIds);
        if (isset($this->memo[$cacheKey])) {
            return $this->memo[$cacheKey];
        }

        $this->batchCounts($facets, $filters, $searchIds);

        if (isset($this->memo[$cacheKey])) {
            return $this->memo[$cacheKey];
        }

        $candidates = $this->filter->resolve($facets, $filters, $facet->slug, $searchIds);
        $counts = $this->index->valueCounts($facet->indexKey(), $candidates);

        $this->memo[$cacheKey] = $counts;
        return $counts;
    }

    /**
     * @param array<string, string> $filters
     * @param array<int, int>|null $searchIds
     */
    private function cacheKey(string $slug, array $filters, ?array $searchIds): string
    {
        return $slug . '|' . md5((string) wp_json_encode([$filters, $searchIds]));
    }

    /**
     * @return array{min: float, max: float}
     */
    public function priceBounds(Facet $facet): array
    {
        return $this->index->numericBounds($facet->indexKey());
    }
}
