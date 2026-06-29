<?php

declare(strict_types=1);

namespace Sieve\Service;

defined('ABSPATH') || exit;

/**
 * Discovers the facet sources available on this store so the admin facet builder
 * can offer them without the user typing anything. This is the "zero-config"
 * groundwork: taxonomies, global product attributes, and the built-in WooCommerce
 * fields (price, stock, on sale, rating) are all auto-detected.
 */
final class FacetCatalog
{
    /**
     * @return array<int, array{source: string, label: string, suggested_type: string, group: string}>
     */
    public function available(): array
    {
        $sources = [];

        // Core product taxonomies.
        $sources[] = $this->entry('tax:product_cat', __('Product category', 'sieve'), 'checkbox', 'taxonomy');
        $sources[] = $this->entry('tax:product_tag', __('Product tag', 'sieve'), 'checkbox', 'taxonomy');

        // Global product attributes (pa_*).
        if (function_exists('wc_get_attribute_taxonomies')) {
            foreach (wc_get_attribute_taxonomies() as $attribute) {
                $taxonomy = 'pa_' . $attribute->attribute_name;
                /* translators: %s: attribute label. */
                $label = sprintf(__('Attribute: %s', 'sieve'), $attribute->attribute_label);
                $sources[] = $this->entry('tax:' . $taxonomy, $label, 'checkbox', 'attribute');
            }
        }

        // Built-in WooCommerce fields.
        $sources[] = $this->entry('price', __('Price', 'sieve'), 'range_slider', 'field');
        $sources[] = $this->entry('stock', __('Stock status', 'sieve'), 'checkbox', 'field');
        $sources[] = $this->entry('on_sale', __('On sale', 'sieve'), 'checkbox', 'field');
        $sources[] = $this->entry('rating', __('Average rating', 'sieve'), 'checkbox', 'field');
        $sources[] = $this->entry('search', __('Search box', 'sieve'), 'search', 'field');

        /**
         * Filters the auto-discovered facet sources for the admin builder.
         *
         * @param array<int, array{source: string, label: string, suggested_type: string, group: string}> $sources
         */
        $filtered = apply_filters('sieve_facet_catalog', $sources);

        return is_array($filtered) ? array_values($filtered) : $sources;
    }

    /**
     * @return array{source: string, label: string, suggested_type: string, group: string}
     */
    private function entry(string $source, string $label, string $type, string $group): array
    {
        return [
            'source' => $source,
            'label' => $label,
            'suggested_type' => $type,
            'group' => $group,
        ];
    }
}
