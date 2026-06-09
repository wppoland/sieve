<?php

declare(strict_types=1);

namespace Sieve\Service;

defined('ABSPATH') || exit;

use Sieve\Enum\FacetType;
use Sieve\Model\Facet;

/**
 * Renders a single facet to accessible, zero-CLS HTML. Every widget is a real
 * form control with proper labels and ARIA, so it works without JavaScript and
 * the frontend script only enhances it.
 */
final class FacetRenderer
{
    /**
     * @param array<string, int> $counts        value => count (already dependent)
     * @param array<int, string> $selected       currently selected values
     * @param array{min: float, max: float}|null $bounds price bounds for sliders
     */
    public function render(Facet $facet, array $counts, array $selected, ?array $bounds = null): string
    {
        $body = match ($facet->type) {
            FacetType::RangeSlider => $this->renderRange($facet, $selected, $bounds),
            FacetType::Search => $this->renderSearch($facet, $selected),
            FacetType::Dropdown => $this->renderDropdown($facet, $counts, $selected),
            FacetType::Swatch => $this->renderSwatch($facet, $counts, $selected),
            FacetType::Hierarchy => $this->renderHierarchy($facet, $counts, $selected),
            default => $this->renderChoices($facet, $counts, $selected),
        };

        if ('' === $body) {
            return '';
        }

        return sprintf(
            '<div class="sieve-facet sieve-facet--%1$s" data-sieve-facet="%2$s" data-sieve-source="%3$s">'
                . '<h3 class="sieve-facet__title">%4$s</h3>%5$s</div>',
            esc_attr($facet->type->value),
            esc_attr($facet->slug),
            esc_attr($facet->source),
            esc_html($facet->label),
            $body,
        );
    }

    /**
     * Human-readable label for one facet value (also used by the active chips).
     */
    public function valueLabel(Facet $facet, string $value): string
    {
        $taxonomy = $facet->taxonomy();
        if (null !== $taxonomy) {
            $term = get_term_by('slug', $value, $taxonomy);
            if ($term instanceof \WP_Term) {
                return $term->name;
            }
            return $value;
        }

        return match ($facet->source) {
            'stock' => $this->stockLabel($value),
            'on_sale' => __('On sale', 'sieve'),
            'rating' => sprintf(
                /* translators: %s: number of stars. */
                _n('%s star', '%s stars', (int) $value, 'sieve'),
                number_format_i18n((int) $value)
            ),
            default => $value,
        };
    }

    /**
     * @param array<string, int> $counts
     * @param array<int, string> $selected
     */
    private function renderChoices(Facet $facet, array $counts, array $selected): string
    {
        if (empty($counts)) {
            return '';
        }

        $isRadio = FacetType::Radio === $facet->type;
        $inputType = $isRadio ? 'radio' : 'checkbox';
        $name = UrlService::PREFIX . $facet->slug . ($isRadio ? '' : '[]');

        $items = '';
        foreach ($this->sortValues($facet, $counts) as $value) {
            $checked = in_array($value, $selected, true);
            $items .= sprintf(
                '<li class="sieve-choice"><label><input type="%1$s" name="%2$s" value="%3$s"%4$s>'
                    . '<span class="sieve-choice__label">%5$s</span>'
                    . '<span class="sieve-choice__count">%6$s</span></label></li>',
                esc_attr($inputType),
                esc_attr($name),
                esc_attr($value),
                $checked ? ' checked' : '',
                esc_html($this->valueLabel($facet, $value)),
                esc_html(number_format_i18n($counts[$value])),
            );
        }

        return '<ul class="sieve-choices" role="group">' . $items . '</ul>';
    }

    /**
     * @param array<string, int> $counts
     * @param array<int, string> $selected
     */
    private function renderDropdown(Facet $facet, array $counts, array $selected): string
    {
        if (empty($counts)) {
            return '';
        }

        $current = $selected[0] ?? '';
        $options = sprintf('<option value="">%s</option>', esc_html__('Any', 'sieve'));

        foreach ($this->sortValues($facet, $counts) as $value) {
            $options .= sprintf(
                '<option value="%1$s"%2$s>%3$s (%4$s)</option>',
                esc_attr($value),
                selected($current, $value, false),
                esc_html($this->valueLabel($facet, $value)),
                esc_html(number_format_i18n($counts[$value])),
            );
        }

        return sprintf(
            '<select class="sieve-dropdown" name="%1$s">%2$s</select>',
            esc_attr(UrlService::PREFIX . $facet->slug),
            $options,
        );
    }

    /**
     * @param array<int, string> $selected
     * @param array{min: float, max: float}|null $bounds
     */
    private function renderRange(Facet $facet, array $selected, ?array $bounds): string
    {
        $min = $bounds['min'] ?? 0.0;
        $max = $bounds['max'] ?? 0.0;
        $current = $selected[0] ?? '';
        $parts = explode('-', $current, 2);
        $curMin = isset($parts[0]) && '' !== $parts[0] ? (float) $parts[0] : $min;
        $curMax = isset($parts[1]) && '' !== $parts[1] ? (float) $parts[1] : $max;

        return sprintf(
            '<div class="sieve-range" data-min="%1$s" data-max="%2$s">'
                . '<div class="sieve-range__inputs">'
                . '<label class="screen-reader-text" for="sieve-%3$s-min">%8$s</label>'
                . '<input type="number" id="sieve-%3$s-min" class="sieve-range__min" inputmode="decimal" min="%1$s" max="%2$s" step="1" value="%4$s">'
                . '<span class="sieve-range__sep" aria-hidden="true">&ndash;</span>'
                . '<label class="screen-reader-text" for="sieve-%3$s-max">%9$s</label>'
                . '<input type="number" id="sieve-%3$s-max" class="sieve-range__max" inputmode="decimal" min="%1$s" max="%2$s" step="1" value="%5$s">'
                . '</div>'
                . '<input type="hidden" name="%6$s" class="sieve-range__value" value="%7$s">'
                . '</div>',
            esc_attr((string) $min),
            esc_attr((string) $max),
            esc_attr($facet->slug),
            esc_attr((string) $curMin),
            esc_attr((string) $curMax),
            esc_attr(UrlService::PREFIX . $facet->slug),
            esc_attr($current),
            esc_html__('Minimum price', 'sieve'),
            esc_html__('Maximum price', 'sieve'),
        );
    }

    /**
     * @param array<int, string> $selected
     */
    private function renderSearch(Facet $facet, array $selected): string
    {
        $current = $selected[0] ?? '';
        return sprintf(
            '<input type="search" class="sieve-search" name="%1$s" value="%2$s" placeholder="%3$s" aria-label="%4$s">',
            esc_attr(UrlService::PREFIX . 'q'),
            esc_attr($current),
            esc_attr__('Search products', 'sieve'),
            esc_attr($facet->label),
        );
    }

    /**
     * Color / image swatches for a taxonomy facet (e.g. an attribute like colour).
     * Each option is a real checkbox visually replaced by a swatch, so it stays
     * keyboard accessible and works without JavaScript.
     *
     * @param array<string, int> $counts
     * @param array<int, string> $selected
     */
    private function renderSwatch(Facet $facet, array $counts, array $selected): string
    {
        if (empty($counts)) {
            return '';
        }

        $taxonomy = $facet->taxonomy();
        $name = UrlService::PREFIX . $facet->slug . '[]';

        $items = '';
        foreach ($this->sortValues($facet, $counts) as $value) {
            $checked = in_array($value, $selected, true);
            $label = $this->valueLabel($facet, $value);

            $items .= sprintf(
                '<li class="sieve-swatch"><label title="%5$s">'
                    . '<input type="checkbox" class="sieve-swatch__input screen-reader-text" name="%1$s" value="%2$s"%3$s>'
                    . '%4$s'
                    . '<span class="sieve-swatch__label">%6$s</span>'
                    . '<span class="sieve-swatch__count">%7$s</span></label></li>',
                esc_attr($name),
                esc_attr($value),
                $checked ? ' checked' : '',
                $this->swatchVisual($taxonomy, $value, $label),
                esc_attr($label),
                esc_html($label),
                esc_html(number_format_i18n($counts[$value])),
            );
        }

        return '<ul class="sieve-swatches" role="group">' . $items . '</ul>';
    }

    private function swatchVisual(?string $taxonomy, string $value, string $label): string
    {
        $image = null !== $taxonomy ? $this->swatchImage($taxonomy, $value) : '';
        if ('' !== $image) {
            return sprintf(
                '<span class="sieve-swatch__visual sieve-swatch__visual--image"><img src="%1$s" alt="%2$s" loading="lazy" width="40" height="40"></span>',
                esc_url($image),
                esc_attr($label),
            );
        }

        $color = null !== $taxonomy ? $this->swatchColor($taxonomy, $value) : '';
        if ('' === $color) {
            $color = $this->guessColor($label);
        }
        if ('' !== $color) {
            return sprintf(
                '<span class="sieve-swatch__visual sieve-swatch__visual--color" style="background-color:%1$s"></span>',
                esc_attr($color),
            );
        }

        return sprintf(
            '<span class="sieve-swatch__visual sieve-swatch__visual--text">%1$s</span>',
            esc_html(function_exists('mb_substr') ? mb_substr($label, 0, 2) : substr($label, 0, 2)),
        );
    }

    private function swatchColor(string $taxonomy, string $value): string
    {
        $term = get_term_by('slug', $value, $taxonomy);
        if (! $term instanceof \WP_Term) {
            return '';
        }
        $color = get_term_meta($term->term_id, 'sieve_swatch_color', true);
        return is_string($color) ? trim($color) : '';
    }

    private function swatchImage(string $taxonomy, string $value): string
    {
        $term = get_term_by('slug', $value, $taxonomy);
        if (! $term instanceof \WP_Term) {
            return '';
        }
        $image = get_term_meta($term->term_id, 'sieve_swatch_image', true);
        if (is_numeric($image)) {
            $url = wp_get_attachment_image_url((int) $image, 'thumbnail');
            return is_string($url) ? $url : '';
        }
        return is_string($image) && '' !== $image ? $image : '';
    }

    /**
     * Best-effort colour from a common colour name, so colour attributes show as
     * swatches without any per-term configuration.
     */
    private function guessColor(string $label): string
    {
        $map = [
            'black' => '#000000', 'czarny' => '#000000',
            'white' => '#ffffff', 'bialy' => '#ffffff', 'biały' => '#ffffff',
            'red' => '#e11d48', 'czerwony' => '#e11d48',
            'green' => '#16a34a', 'zielony' => '#16a34a',
            'blue' => '#2563eb', 'niebieski' => '#2563eb',
            'yellow' => '#facc15', 'zolty' => '#facc15', 'żółty' => '#facc15',
            'orange' => '#f97316', 'pomaranczowy' => '#f97316',
            'purple' => '#9333ea', 'fioletowy' => '#9333ea',
            'pink' => '#ec4899', 'rozowy' => '#ec4899', 'różowy' => '#ec4899',
            'grey' => '#9ca3af', 'gray' => '#9ca3af', 'szary' => '#9ca3af',
            'brown' => '#92400e', 'brazowy' => '#92400e', 'brązowy' => '#92400e',
            'navy' => '#1e3a8a', 'granatowy' => '#1e3a8a',
            'beige' => '#e7d8b1', 'bezowy' => '#e7d8b1', 'beżowy' => '#e7d8b1',
            'gold' => '#d4af37', 'zloty' => '#d4af37', 'złoty' => '#d4af37',
            'silver' => '#c0c0c0', 'srebrny' => '#c0c0c0',
        ];
        $key = function_exists('mb_strtolower') ? mb_strtolower(trim($label)) : strtolower(trim($label));
        return $map[$key] ?? '';
    }

    /**
     * Hierarchical (tree) rendering for a hierarchical taxonomy such as product
     * categories. Shows only branches that lead to available results.
     *
     * @param array<string, int> $counts
     * @param array<int, string> $selected
     */
    private function renderHierarchy(Facet $facet, array $counts, array $selected): string
    {
        $taxonomy = $facet->taxonomy();
        if (null === $taxonomy || empty($counts) || ! is_taxonomy_hierarchical($taxonomy)) {
            return $this->renderChoices($facet, $counts, $selected);
        }

        /** @var array<int, \WP_Term> $present */
        $present = [];
        foreach (array_keys($counts) as $slug) {
            $term = get_term_by('slug', $slug, $taxonomy);
            if (! $term instanceof \WP_Term) {
                continue;
            }
            $present[$term->term_id] = $term;
            foreach (get_ancestors($term->term_id, $taxonomy) as $ancestorId) {
                if (! isset($present[$ancestorId])) {
                    $ancestor = get_term($ancestorId, $taxonomy);
                    if ($ancestor instanceof \WP_Term) {
                        $present[$ancestorId] = $ancestor;
                    }
                }
            }
        }

        if ([] === $present) {
            return '';
        }

        /** @var array<int, array<int, int>> $children */
        $children = [];
        foreach ($present as $term) {
            $children[$term->parent][] = $term->term_id;
        }

        $name = UrlService::PREFIX . $facet->slug . '[]';

        return $this->renderTreeLevel(0, $children, $present, $counts, $selected, $name);
    }

    /**
     * @param array<int, array<int, int>> $children parent term id => child term ids
     * @param array<int, \WP_Term> $present
     * @param array<string, int> $counts
     * @param array<int, string> $selected
     */
    private function renderTreeLevel(int $parentId, array $children, array $present, array $counts, array $selected, string $name): string
    {
        if (empty($children[$parentId])) {
            return '';
        }

        $ids = $children[$parentId];
        usort($ids, static fn (int $a, int $b): int => strcasecmp($present[$a]->name, $present[$b]->name));

        $items = '';
        foreach ($ids as $id) {
            $term = $present[$id];
            $count = $counts[$term->slug] ?? 0;
            $checked = in_array($term->slug, $selected, true);
            $sub = $this->renderTreeLevel($id, $children, $present, $counts, $selected, $name);

            $items .= sprintf(
                '<li class="sieve-tree__item"><label><input type="checkbox" name="%1$s" value="%2$s"%3$s>'
                    . '<span class="sieve-choice__label">%4$s</span>'
                    . '<span class="sieve-choice__count">%5$s</span></label>%6$s</li>',
                esc_attr($name),
                esc_attr($term->slug),
                $checked ? ' checked' : '',
                esc_html($term->name),
                $count > 0 ? esc_html(number_format_i18n($count)) : '',
                $sub,
            );
        }

        return '<ul class="sieve-tree" role="group">' . $items . '</ul>';
    }

    /**
     * Sort taxonomy values by label, fixed orders for the built-in fields.
     *
     * @param array<string, int> $counts
     * @return array<int, string>
     */
    private function sortValues(Facet $facet, array $counts): array
    {
        $values = array_keys($counts);

        if ('stock' === $facet->source) {
            $order = ['instock', 'onbackorder', 'outofstock'];
            usort($values, static fn ($a, $b): int => array_search($a, $order, true) <=> array_search($b, $order, true));
            return $values;
        }

        if ('rating' === $facet->source) {
            rsort($values, SORT_NUMERIC);
            return $values;
        }

        usort($values, fn ($a, $b): int => strcasecmp($this->valueLabel($facet, $a), $this->valueLabel($facet, $b)));
        return $values;
    }

    private function stockLabel(string $value): string
    {
        return match ($value) {
            'instock' => __('In stock', 'sieve'),
            'outofstock' => __('Out of stock', 'sieve'),
            'onbackorder' => __('On backorder', 'sieve'),
            default => $value,
        };
    }
}
