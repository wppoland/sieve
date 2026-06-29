<?php

declare(strict_types=1);

namespace Sieve\Service;

defined('ABSPATH') || exit;

/**
 * Renders the predictive search widget markup shared by the [sieve_search]
 * shortcode and the "Sieve Search" block. It is a real, submittable search form
 * that works without JavaScript (it falls back to the native product search);
 * the typeahead dropdown is progressive enhancement layered on top.
 */
final class SearchRenderer
{
    public function __construct(
        private readonly AppearanceService $appearance,
        private readonly Settings $settings,
    ) {
    }

    /**
     * @param array<string, mixed> $atts
     */
    public function render(array $atts): string
    {
        $config = $this->normalise($atts);

        $preset = $this->appearance->resolveFrom($this->settings->all())['preset'];
        $styleAttr = 'default' === $preset
            ? ''
            : ' data-sieve-style="' . esc_attr($preset) . '"';
        $id = wp_unique_id('sieve-suggest-');
        $listId = $id . '-list';

        $form = sprintf(
            '<form class="sieve-search__form" role="search" method="get" action="%s">',
            esc_url(home_url('/')),
        );

        $input = sprintf(
            '<input type="search" class="sieve-search__input" name="s" value="" autocomplete="off"'
                . ' placeholder="%1$s" aria-label="%2$s" role="combobox" aria-expanded="false"'
                . ' aria-autocomplete="list" aria-controls="%3$s" aria-haspopup="listbox"'
                . ' data-sieve-search-input>',
            esc_attr($config['placeholder']),
            esc_attr($config['label']),
            esc_attr($listId),
        );

        $hidden = '<input type="hidden" name="post_type" value="product">';

        $button = '';
        if ($config['button']) {
            $button = sprintf(
                '<button type="submit" class="sieve-search__submit">%s</button>',
                esc_html($config['button_text']),
            );
        }

        $results = sprintf(
            '<div id="%1$s" class="sieve-search__results" role="listbox" aria-label="%2$s" hidden'
                . ' data-sieve-search-results></div>',
            esc_attr($listId),
            esc_attr($config['results_label']),
        );

        // Out-of-band polite live region: announces the searching state and the
        // result count to assistive tech. The listbox itself cannot carry a
        // perceivable empty / "N results" message (a non-option child is invalid
        // and pruned by screen readers).
        $status = '<span class="screen-reader-text" role="status" aria-live="polite" data-sieve-search-status></span>';

        $inner = sprintf(
            '<div class="sieve-search__inner">%s%s%s</div>',
            $input,
            $hidden,
            $button,
        );

        return sprintf(
            '<div class="sieve-search" data-sieve-search%6$s data-limit="%1$d" data-min-chars="%2$d"'
                . ' data-in-stock-only="%3$s">%4$s%5$s</form></div>',
            $config['limit'],
            $config['min_chars'],
            $config['in_stock_only'] ? '1' : '0',
            $form,
            $inner . $status . $results,
            $styleAttr,
        );
    }

    /**
     * @param array<string, mixed> $atts
     * @return array{placeholder: string, label: string, results_label: string, button: bool, button_text: string, limit: int, min_chars: int, in_stock_only: bool}
     */
    private function normalise(array $atts): array
    {
        $defaults = [
            'placeholder' => __('Search products', 'sieve'),
            'label' => __('Search products', 'sieve'),
            'results_label' => __('Product search results', 'sieve'),
            'button' => true,
            'button_text' => __('Search', 'sieve'),
            'limit' => 6,
            'min_chars' => 2,
            'in_stock_only' => true,
        ];

        $merged = array_merge($defaults, array_intersect_key($atts, $defaults));

        return [
            'placeholder' => (string) $merged['placeholder'],
            'label' => (string) $merged['label'],
            'results_label' => (string) $merged['results_label'],
            'button' => $this->toBool($merged['button']),
            'button_text' => (string) $merged['button_text'],
            'limit' => max(1, min(20, (int) $merged['limit'])),
            'min_chars' => max(1, min(10, (int) $merged['min_chars'])),
            'in_stock_only' => $this->toBool($merged['in_stock_only']),
        ];
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }
}
