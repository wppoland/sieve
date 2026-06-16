<?php

declare(strict_types=1);

namespace Sieve\Service;

defined('ABSPATH') || exit;

use Sieve\Model\Facet;
use WPPoland\StorefrontKit\Filter\FacetFilterEngine;

/**
 * The single source of truth for a filter render. Both the initial server-side
 * render (shortcode / block) and the AJAX endpoint call run(), so the markup is
 * identical and the frontend can swap fragments in place with zero layout shift.
 *
 * Delegates the filtering work to {@see FacetFilterEngine} when present; Sieve
 * keeps templates, settings and index access local.
 */
final class FilterEngine
{
    private ?FacetFilterEngine $kitEngine = null;

    public function __construct(
        private readonly Settings $settings,
        private readonly FilterService $filter,
        private readonly FacetCountService $counts,
        private readonly FacetRenderer $facetRenderer,
        private readonly ResultsRenderer $resultsRenderer,
        private readonly SearchResolver $resolver,
        private readonly AppearanceService $appearance,
    ) {
    }

    /**
     * @param array{filters: array<string, string>, orderby: string, paged: int, search: string} $request
     * @return array{facets_html: string, toolbar_html: string, results_html: string, pagination_html: string, found: int, count_text: string}
     */
    public function run(array $request): array
    {
        $kit = $this->kitEngine();

        if ($kit instanceof FacetFilterEngine) {
            return $kit->run($request);
        }

        return $this->runLegacy($request);
    }

    /**
     * @param array{filters: array<string, string>, orderby: string, paged: int, search: string} $request
     * @return array{facets_html: string, toolbar_html: string, results_html: string, pagination_html: string, found: int, count_text: string}
     */
    private function runLegacy(array $request): array
    {
        $config = $this->settings->all();
        $facets = $this->facetsForRequest($request);
        $filters = $request['filters'];

        // Resolve the search term to ids ONCE via the shared resolver, then thread
        // the same id set into the grid query and every dependent count so the
        // grid, the counts and the dropdown can never disagree. null = search
        // inactive, [] = matched nothing, int[] = ids.
        $searchIds = $this->resolver->resolve($request['search']);

        $resolved = $this->filter->resolve($facets, $filters, null, $searchIds);
        $results = $this->resultsRenderer->render(
            $resolved,
            $request['orderby'],
            $request['paged'],
            // Pass '' deliberately: the folded index is authoritative for grid
            // search, so no diacritic-sensitive native 's' runs here.
            '',
            (int) $config['per_page'],
            (int) $config['columns'],
        );

        return [
            'facets_html' => $this->renderFacets($facets, $filters, $request['search'], $searchIds),
            'toolbar_html' => $this->renderToolbar($facets, $filters, $request, $results['count_text']),
            'results_html' => $results['html'],
            'pagination_html' => $this->renderPagination($request['paged'], $results['max_pages']),
            'found' => $results['found'],
            'count_text' => $results['count_text'],
        ];
    }

    /**
     * @param array{filters: array<string, string>, orderby: string, paged: int, search: string} $request
     * @return array<int, int>|null
     */
    public function resolveObjectIds(array $request): ?array
    {
        $searchIds = $this->resolver->resolve($request['search']);

        return $this->filter->resolve(
            $this->facetsForRequest($request),
            $request['filters'],
            null,
            $searchIds,
        );
    }

    /**
     * @param array{filters: array<string, string>, orderby: string, paged: int, search: string} $request
     */
    public function renderFacetsForRequest(array $request): string
    {
        $searchIds = $this->resolver->resolve($request['search']);

        return $this->renderFacets(
            $this->facetsForRequest($request),
            $request['filters'],
            $request['search'],
            $searchIds,
        );
    }

    /**
     * @param array{filters: array<string, string>, orderby: string, paged: int, search: string} $request
     */
    public function renderToolbarForRequest(array $request, string $countText): string
    {
        return $this->renderToolbar(
            $this->facetsForRequest($request),
            $request['filters'],
            $request,
            $countText,
        );
    }

    /**
     * @param array<int, int>|null $objectIds
     * @return array{html: string, count_text: string, found: int, max_pages: int}
     */
    public function renderResultsForRequest(?array $objectIds, string $orderby, int $paged, int $perPage): array
    {
        $config = $this->settings->all();

        return $this->resultsRenderer->render(
            $objectIds,
            $orderby,
            $paged,
            '',
            $perPage,
            (int) $config['columns'],
        );
    }

    public function renderPaginationForRequest(int $paged, int $maxPages): string
    {
        return $this->renderPagination($paged, $maxPages);
    }

    public function perPage(): int
    {
        $config = $this->settings->all();

        return max(1, (int) $config['per_page']);
    }

    public function columns(): int
    {
        $config = $this->settings->all();

        return max(1, (int) $config['columns']);
    }

    private function kitEngine(): ?FacetFilterEngine
    {
        if (null !== $this->kitEngine) {
            return $this->kitEngine;
        }

        $this->kitEngine = SieveFilterAdapter::engineFor($this);

        return $this->kitEngine;
    }

    /**
     * Full server-rendered widget for the shortcode / block.
     *
     * @param array{filters: array<string, string>, orderby: string, paged: int, search: string} $request
     */
    public function container(array $request): string
    {
        $kit = $this->kitEngine();

        if ($kit instanceof FacetFilterEngine) {
            return $kit->container(
                $request,
                fn (array $parts, int $columns): string => $this->wrapContainer($parts, $columns, $request['context'] ?? []),
            );
        }

        $parts = $this->runLegacy($request);

        return $this->wrapContainer($parts, $this->columns(), $request['context'] ?? []);
    }

    /**
     * @param array{filters: array<string, string>, orderby: string, paged: int, search: string, context?: array<string, mixed>} $request
     * @return array<int, Facet>
     */
    private function facetsForRequest(array $request): array
    {
        $context = isset($request['context']) && is_array($request['context']) ? $request['context'] : [];

        return $this->settings->facetsForContext($context);
    }

    /**
     * @param array{facets_html: string, toolbar_html: string, results_html: string, pagination_html: string, found: int, count_text: string} $parts
     * @param array{is_shop?: bool, category_id?: int, user_roles?: array<int, string>} $context
     */
    private function wrapContainer(array $parts, int $columns, array $context = []): string
    {
        $config = $this->settings->all();
        $preset = $this->appearance->resolveFrom($config)['preset'];
        $styleAttr = 'default' === $preset
            ? ''
            : ' data-sieve-style="' . esc_attr($preset) . '"';

        $categoryId = isset($context['category_id']) ? max(0, (int) $context['category_id']) : 0;
        $ctxAttr    = $categoryId > 0 ? ' data-sieve-ctx-category="' . esc_attr((string) $categoryId) . '"' : '';
        $ctxAttr   .= ! empty($context['is_shop']) ? ' data-sieve-ctx-shop="1"' : '';

        return sprintf(
            '<div class="sieve-app" data-sieve-app%8$s%11$s style="--sieve-cols:%7$d">'
                . '<form class="sieve-filters" data-sieve-form aria-label="%9$s">'
                . '<button type="button" class="sieve-drawer-toggle" data-sieve-open aria-expanded="false">%1$s</button>'
                . '<div class="sieve-facets" data-sieve-facets>%2$s</div>'
                . '</form>'
                . '<div class="sieve-main">'
                . '<div class="sieve-toolbar" data-sieve-toolbar>%3$s</div>'
                . '<div class="sieve-results-wrap">'
                . '<div class="sieve-results" data-sieve-results>%4$s</div>'
                . '<div class="sieve-loading" aria-hidden="true"><span class="sieve-loading__spinner"></span></div>'
                . '</div>'
                . '<nav class="sieve-pagination" data-sieve-pagination aria-label="%10$s">%5$s</nav>'
                . '</div>'
                . '<div class="sieve-drawer-apply" data-sieve-apply hidden><button type="button" data-sieve-close>%6$s</button></div>'
                . '</div>',
            esc_html__('Filters', 'sieve'),
            $parts['facets_html'],
            $parts['toolbar_html'],
            $parts['results_html'],
            $parts['pagination_html'],
            esc_html__('Show results', 'sieve'),
            $columns,
            $styleAttr,
            esc_attr__('Product filters', 'sieve'),
            esc_attr__('Results pages', 'sieve'),
            $ctxAttr,
        );
    }

    /**
     * @param array<int, Facet> $facets
     * @param array<string, string> $filters
     * @param array<int, int>|null $searchIds Resolved once in run() and threaded
     *        here so counts are search-aware without recomputing per facet.
     */
    private function renderFacets(array $facets, array $filters, string $search, ?array $searchIds): string
    {
        $html = '';
        foreach ($facets as $facet) {
            if ($facet->isSearch()) {
                $selected = '' !== $search ? [$search] : [];
                $html .= $this->facetRenderer->render($facet, [], $selected);
                continue;
            }

            $raw = $filters[$facet->slug] ?? '';
            $selected = '' !== $raw ? explode(',', $raw) : [];

            if ($facet->isPrice()) {
                $html .= $this->facetRenderer->render($facet, [], $selected, $this->counts->priceBounds($facet));
                continue;
            }

            $counts = $this->counts->countsFor($facet, $facets, $filters, $searchIds);
            $html .= $this->facetRenderer->render($facet, $counts, $selected);
        }

        return $html;
    }

    /**
     * @param array<int, Facet> $facets
     * @param array<string, string> $filters
     * @param array{filters: array<string, string>, orderby: string, paged: int, search: string} $request
     */
    private function renderToolbar(array $facets, array $filters, array $request, string $countText): string
    {
        return sprintf(
            '<span class="sieve-count" data-sieve-count role="status" aria-live="polite">%1$s</span>'
                . '<div class="sieve-toolbar__right">%2$s%3$s</div>'
                . '<div class="sieve-chips" data-sieve-chips>%4$s</div>',
            esc_html($countText),
            $this->renderSort($request['orderby']),
            $this->renderReset($filters, $request['search']),
            $this->renderChips($facets, $filters, $request['search']),
        );
    }

    private function renderSort(string $current): string
    {
        $options = [
            '' => __('Default sorting', 'sieve'),
            'popularity' => __('Sort by popularity', 'sieve'),
            'rating' => __('Sort by average rating', 'sieve'),
            'date' => __('Sort by latest', 'sieve'),
            'price' => __('Sort by price: low to high', 'sieve'),
            'price-desc' => __('Sort by price: high to low', 'sieve'),
        ];

        $opts = '';
        foreach ($options as $value => $label) {
            $opts .= sprintf(
                '<option value="%1$s"%2$s>%3$s</option>',
                esc_attr($value),
                selected($current, $value, false),
                esc_html($label),
            );
        }

        return sprintf(
            '<label class="sieve-sort"><span class="screen-reader-text">%1$s</span>'
                . '<select name="%2$s" data-sieve-sort>%3$s</select></label>',
            esc_html__('Sort products', 'sieve'),
            esc_attr(UrlService::PREFIX . 'orderby'),
            $opts,
        );
    }

    /**
     * @param array<string, string> $filters
     */
    private function renderReset(array $filters, string $search): string
    {
        if (empty($filters) && '' === $search) {
            return '';
        }

        return sprintf(
            '<button type="button" class="sieve-reset" data-sieve-reset>%s</button>',
            esc_html__('Clear all', 'sieve'),
        );
    }

    /**
     * @param array<int, Facet> $facets
     * @param array<string, string> $filters
     */
    private function renderChips(array $facets, array $filters, string $search): string
    {
        $chips = '';

        foreach ($facets as $facet) {
            $raw = $filters[$facet->slug] ?? '';
            if ('' === $raw) {
                continue;
            }

            if ($facet->isPrice()) {
                $chips .= $this->chip($facet->slug, '', $raw);
                continue;
            }

            foreach (explode(',', $raw) as $value) {
                $chips .= $this->chip($facet->slug, $value, $this->facetRenderer->valueLabel($facet, $value));
            }
        }

        if ('' !== $search) {
            $chips .= $this->chip('q', '', $search);
        }

        if ('' === $chips) {
            return '';
        }

        // A leading label so the chip row reads as "Active filters: …" for both
        // sighted shoppers and screen readers.
        return sprintf(
            '<span class="sieve-chips__label">%s</span>%s',
            esc_html__('Active filters:', 'sieve'),
            $chips,
        );
    }

    private function chip(string $slug, string $value, string $label): string
    {
        return sprintf(
            '<button type="button" class="sieve-chip" data-sieve-chip data-facet="%1$s" data-value="%2$s" aria-label="%4$s">'
                . '<span class="sieve-chip__text">%3$s</span>'
                . '<span class="sieve-chip__x" aria-hidden="true">&times;</span></button>',
            esc_attr($slug),
            esc_attr($value),
            esc_html($label),
            /* translators: %s: the active filter being removed. */
            esc_attr(sprintf(__('Remove filter: %s', 'sieve'), $label)),
        );
    }

    private function renderPagination(int $paged, int $maxPages): string
    {
        if ($maxPages < 2) {
            return '';
        }

        $links = '';
        for ($i = 1; $i <= $maxPages; $i++) {
            $links .= sprintf(
                '<button type="button" class="sieve-page%1$s" data-sieve-page="%2$d"%3$s>%4$s</button>',
                $i === $paged ? ' is-current' : '',
                $i,
                $i === $paged ? ' aria-current="page"' : '',
                esc_html(number_format_i18n($i)),
            );
        }

        return $links;
    }
}
