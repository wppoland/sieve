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
