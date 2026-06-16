<?php

declare(strict_types=1);

namespace Sieve\Service;

defined('ABSPATH') || exit;

use Sieve\Enum\FacetType;

/**
 * Lists facet presentation types for the admin builder. PRO add-ons extend the
 * list via {@see sieve_facet_types}.
 */
final class FacetTypeRegistry
{
    /**
     * @return array<int, array{value: string, label: string, help: string}>
     */
    public function all(): array
    {
        $types = [];
        foreach (FacetType::builder() as $type) {
            $types[] = [
                'value' => $type->value,
                'label' => $type->label(),
                'help'  => $this->helpFor($type),
            ];
        }

        /**
         * Filters the facet type options exposed in the admin builder.
         *
         * @param array<int, array{value: string, label: string, help: string}> $types Built-in types.
         */
        $filtered = apply_filters('sieve_facet_types', $types);

        return is_array($filtered) ? array_values($filtered) : $types;
    }

    private function helpFor(FacetType $type): string
    {
        return match ($type) {
            FacetType::Checkbox => __('Multiple choices can be selected at once. Best for most attributes.', 'sieve'),
            FacetType::Radio => __('Only one choice at a time. Good for mutually exclusive options.', 'sieve'),
            FacetType::Dropdown => __('A compact select menu. Saves space when there are many options.', 'sieve'),
            FacetType::Swatch => __('Colour or image chips instead of text labels. Great for colour and size attributes.', 'sieve'),
            FacetType::Hierarchy => __('Nested categories shown as an expandable tree.', 'sieve'),
            FacetType::Autocomplete => __('A search box that filters the facet options as the shopper types.', 'sieve'),
            FacetType::AzIndex => __('An A-Z bar that filters options by first letter.', 'sieve'),
            FacetType::RangeSlider => __('A min/max range. Used for price and other numeric values.', 'sieve'),
            FacetType::Search => __('A live search box that narrows the product grid as shoppers type.', 'sieve'),
            FacetType::Sort, FacetType::Pager, FacetType::Reset, FacetType::ActiveChips => '',
            FacetType::StarRating => __('Visual star rows for average rating. Requires Sieve Pro for storefront rendering.', 'sieve'),
        };
    }
}
