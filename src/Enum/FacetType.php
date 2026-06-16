<?php

declare(strict_types=1);

namespace Sieve\Enum;

defined('ABSPATH') || exit;

/**
 * Built-in facet types. MVP ships the first block; later types (swatch, image,
 * hierarchy, range-list, autocomplete) are added without breaking the contract.
 */
enum FacetType: string
{
    case Checkbox = 'checkbox';
    case Radio = 'radio';
    case Dropdown = 'dropdown';
    case Swatch = 'swatch';
    case Hierarchy = 'hierarchy';
    case Autocomplete = 'autocomplete';
    case AzIndex = 'az_index';
    case RangeSlider = 'range_slider';
    case Search = 'search';
    case Sort = 'sort';
    case Pager = 'pager';
    case Reset = 'reset';
    case ActiveChips = 'active_chips';
    case StarRating = 'star_rating';

    /**
     * Facet types selectable in the admin facet builder.
     *
     * @return array<int, self>
     */
    public static function builder(): array
    {
        return [
            self::Checkbox,
            self::Radio,
            self::Dropdown,
            self::Swatch,
            self::Hierarchy,
            self::Autocomplete,
            self::AzIndex,
            self::RangeSlider,
            self::Search,
        ];
    }

    /**
     * Facet types shipped in the FREE MVP.
     *
     * @return array<int, self>
     */
    public static function mvp(): array
    {
        return [
            self::Checkbox,
            self::Radio,
            self::Dropdown,
            self::Swatch,
            self::Hierarchy,
            self::Autocomplete,
            self::AzIndex,
            self::RangeSlider,
            self::Search,
            self::Sort,
            self::Pager,
            self::Reset,
            self::ActiveChips,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::Checkbox => __('Checkboxes', 'sieve'),
            self::Radio => __('Radio', 'sieve'),
            self::Dropdown => __('Dropdown', 'sieve'),
            self::Swatch => __('Swatches (color / image)', 'sieve'),
            self::Hierarchy => __('Hierarchy (tree)', 'sieve'),
            self::Autocomplete => __('Autocomplete (searchable options)', 'sieve'),
            self::AzIndex => __('A-Z index', 'sieve'),
            self::RangeSlider => __('Range slider', 'sieve'),
            self::Search => __('Search', 'sieve'),
            self::Sort => __('Sort', 'sieve'),
            self::Pager => __('Pagination', 'sieve'),
            self::Reset => __('Reset', 'sieve'),
            self::ActiveChips => __('Active filters', 'sieve'),
            self::StarRating => __('Star rating', 'sieve'),
        };
    }
}
