<?php

declare(strict_types=1);

namespace Sieve\Service;

defined('ABSPATH') || exit;

use Sieve\Model\Facet;

/**
 * The single source of truth for a filter render. Both the initial server-side
 * render (shortcode / block) and the AJAX endpoint call run(), so the markup is
 * identical and the frontend can swap fragments in place with zero layout shift.
 */
final class FilterEngine
{
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
        $config = $this->settings->all();
        $facets = $this->settings->facets();
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
     * Full server-rendered widget for the shortcode / block.
     *
     * @param array{filters: array<string, string>, orderby: string, paged: int, search: string} $request
     */
    public function container(array $request): string
    {
        $parts = $this->run($request);
        $config = $this->settings->all();
        $columns = max(1, (int) $config['columns']);

        $preset = $this->appearance->resolveFrom($config)['preset'];
        $styleAttr = 'default' === $preset
            ? ''
            : ' data-sieve-style="' . esc_attr($preset) . '"';

        return sprintf(
            '<div class="sieve-app" data-sieve-app%8$s style="--sieve-cols:%7$d">'
                . '<form class="sieve-filters" data-sieve-form>'
                . '<button type="button" class="sieve-drawer-toggle" data-sieve-open aria-expanded="false">%1$s</button>'
                . '<div class="sieve-facets" data-sieve-facets>%2$s</div>'
                . '</form>'
                . '<div class="sieve-main">'
                . '<div class="sieve-toolbar" data-sieve-toolbar>%3$s</div>'
                . '<div class="sieve-results" data-sieve-results aria-live="polite">%4$s</div>'
                . '<nav class="sieve-pagination" data-sieve-pagination>%5$s</nav>'
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
            '<span class="sieve-count" data-sieve-count>%1$s</span>'
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

        return $chips;
    }

    private function chip(string $slug, string $value, string $label): string
    {
        return sprintf(
            '<button type="button" class="sieve-chip" data-sieve-chip data-facet="%1$s" data-value="%2$s">'
                . '%3$s<span class="sieve-chip__x" aria-hidden="true">&times;</span></button>',
            esc_attr($slug),
            esc_attr($value),
            esc_html($label),
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
