<?php

declare(strict_types=1);

namespace Sieve\Service;

defined('ABSPATH') || exit;

use WPPoland\StorefrontKit\Filter\FacetFilterEngine;

/**
 * Wires the namespace-neutral {@see FacetFilterEngine} with Sieve settings,
 * index resolution and template fragments. Keeps option keys, text domain and
 * markup in this plugin; the kit owns only the orchestration contract.
 */
final class SieveFilterAdapter
{
    /**
     * @internal Called from {@see FilterEngine} only.
     */
    public static function engineFor(FilterEngine $host): ?FacetFilterEngine
    {
        if (! class_exists(FacetFilterEngine::class)) {
            return null;
        }

        return new FacetFilterEngine(
            resolveObjectIds: static fn (array $request): ?array => $host->resolveObjectIds($request),
            renderFacets: static fn (array $request, ?array $objectIds): string => $host->renderFacetsForRequest($request),
            renderToolbar: static fn (array $request, ?array $objectIds, string $countText): string => $host->renderToolbarForRequest($request, $countText),
            renderResults: static fn (?array $objectIds, string $orderby, int $paged, int $perPage): array => $host->renderResultsForRequest($objectIds, $orderby, $paged, $perPage),
            renderPagination: static fn (int $paged, int $maxPages): string => $host->renderPaginationForRequest($paged, $maxPages),
            perPage: static fn (): int => $host->perPage(),
            columns: static fn (): int => $host->columns(),
        );
    }
}
