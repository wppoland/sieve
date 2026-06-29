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
        // The cache key must include the search ids: within one request lifecycle
        // the same facet can be counted with and without a search, and the counts
        // differ (search-aware). Omitting it would serve stale counts.
        $cacheKey = $facet->slug . '|' . md5((string) wp_json_encode([$filters, $searchIds]));
        if (isset($this->memo[$cacheKey])) {
            return $this->memo[$cacheKey];
        }

        $candidates = $this->filter->resolve($facets, $filters, $facet->slug, $searchIds);
        $counts = $this->index->valueCounts($facet->indexKey(), $candidates);

        $this->memo[$cacheKey] = $counts;
        return $counts;
    }

    /**
     * @return array{min: float, max: float}
     */
    public function priceBounds(Facet $facet): array
    {
        return $this->index->numericBounds($facet->indexKey());
    }
}
