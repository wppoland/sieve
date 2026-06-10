<?php

declare(strict_types=1);

namespace Sieve\Service;

defined('ABSPATH') || exit;

use Sieve\Model\Facet;
use Sieve\Repository\IndexRepository;

/**
 * Resolves the set of product IDs that satisfy the active filter selection by
 * intersecting per-facet matches from the pre-built index. OR within a facet
 * (multiple checked values), AND across facets - the behaviour shoppers expect.
 */
final class FilterService
{
    public function __construct(private readonly IndexRepository $index)
    {
    }

    /**
     * Resolve matching object IDs for a selection.
     *
     * @param array<int, Facet> $facets   Configured facets.
     * @param array<string, string> $filters Raw filter values keyed by facet slug.
     * @param string|null $exclude         Slug to ignore (for dependent counts).
     * @param array<int, int>|null $searchIds Resolved search ids: null = search
     *        inactive (no constraint), [] = search matched nothing, int[] = ids.
     * @return array<int, int>|null        Matching IDs, or null when unrestricted.
     */
    public function resolve(array $facets, array $filters, ?string $exclude = null, ?array $searchIds = null): ?array
    {
        $sets = [];

        foreach ($facets as $facet) {
            if ($facet->slug === $exclude || $facet->isSearch()) {
                continue;
            }

            $raw = $filters[$facet->slug] ?? '';
            if ('' === $raw) {
                continue;
            }

            $sets[] = $facet->isPrice()
                ? $this->resolvePrice($facet, $raw)
                : $this->index->objectsForValues($facet->indexKey(), $this->splitValues($raw));
        }

        if (empty($sets)) {
            $result = null;
        } else {
            $result = array_shift($sets);
            foreach ($sets as $set) {
                $result = array_values(array_intersect($result, $set));
                if (empty($result)) {
                    $result = [];
                    break;
                }
            }
        }

        // Search arrives as already-resolved ids (not as a facet). Intersect it
        // into the facet result so the grid and dependent counts apply it
        // identically. null => search inactive; [] => constrain to nothing.
        if (null !== $searchIds) {
            $result = (null === $result)
                ? $searchIds
                : array_values(array_intersect($result, $searchIds));
        }

        return $result;
    }

    /**
     * @return array<int, int>
     */
    private function resolvePrice(Facet $facet, string $raw): array
    {
        [$min, $max] = $this->parseRange($raw);
        return $this->index->objectsForRange($facet->indexKey(), $min, $max);
    }

    /**
     * @return array{0: float|null, 1: float|null}
     */
    private function parseRange(string $raw): array
    {
        $parts = explode('-', $raw, 2);
        $min = isset($parts[0]) && '' !== $parts[0] ? (float) $parts[0] : null;
        $max = isset($parts[1]) && '' !== $parts[1] ? (float) $parts[1] : null;
        return [$min, $max];
    }

    /**
     * @return array<int, string>
     */
    private function splitValues(string $raw): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $raw)), static fn ($v): bool => '' !== $v));
    }
}
