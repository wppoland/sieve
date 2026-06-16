<?php

declare(strict_types=1);

namespace Sieve\Service;

defined('ABSPATH') || exit;

use Sieve\Model\Facet;
use Sieve\Support\FacetContext;

/**
 * Reads and writes the plugin settings (the configured facet set + layout
 * options), stored in a single wp_options row. Ships a sensible "WooCommerce
 * shop" preset so a fresh install filters out of the box.
 */
final class Settings
{
    public const OPTION = 'sieve_settings';

    public function __construct(private readonly AppearanceService $appearance)
    {
    }

    /**
     * @return array{facets: array<int, array<string, mixed>>, per_page: int, columns: int, appearance: array{preset: string, colors: array<string, string>}}
     */
    public function all(): array
    {
        $stored = get_option(self::OPTION, []);
        if (! is_array($stored) || empty($stored['facets'])) {
            return $this->defaults();
        }

        return [
            'facets' => is_array($stored['facets']) ? array_values($stored['facets']) : [],
            'per_page' => isset($stored['per_page']) ? max(1, (int) $stored['per_page']) : 12,
            'columns' => isset($stored['columns']) ? max(1, (int) $stored['columns']) : 3,
            'appearance' => isset($stored['appearance']) && is_array($stored['appearance'])
                ? $this->appearance->normalize($stored['appearance'])
                : $this->appearance->defaults(),
        ];
    }

    /**
     * @param array<string, mixed> $value
     */
    public function save(array $value): void
    {
        $facets = [];
        if (isset($value['facets']) && is_array($value['facets'])) {
            foreach ($value['facets'] as $facet) {
                if (is_array($facet)) {
                    $facets[] = Facet::fromArray($facet)->toArray();
                }
            }
        }

        update_option(self::OPTION, [
            'facets' => $facets,
            'per_page' => isset($value['per_page']) ? max(1, (int) $value['per_page']) : 12,
            'columns' => isset($value['columns']) ? max(1, (int) $value['columns']) : 3,
            'appearance' => $this->appearance->normalize(
                isset($value['appearance']) && is_array($value['appearance']) ? $value['appearance'] : [],
            ),
        ]);
    }

    /**
     * Configured facets as value objects.
     *
     * @return array<int, Facet>
     */
    public function facets(): array
    {
        $facets = [];
        foreach ($this->all()['facets'] as $raw) {
            $facets[] = Facet::fromArray($raw);
        }
        return $facets;
    }

    /**
     * Facets visible for the current page / shopper context.
     *
     * @param array<string, mixed> $context See {@see FacetContext}.
     * @return array<int, Facet>
     */
    public function facetsForContext(array $context = []): array
    {
        $facets = $this->facets();
        $context = FacetContext::normalize($context);

        /**
         * Filters the facet list before render and resolution.
         *
         * @param array<int, Facet> $facets  Configured facets.
         * @param array{is_shop: bool, category_id: int, user_roles: array<int, string>} $context Page context.
         */
        $filtered = apply_filters('sieve_facets', $facets, $context);

        return is_array($filtered) ? array_values($filtered) : $facets;
    }

    /**
     * The default "WooCommerce shop" preset.
     *
     * @return array{facets: array<int, array<string, mixed>>, per_page: int, columns: int, appearance: array{preset: string, colors: array<string, string>}}
     */
    public function defaults(): array
    {
        return [
            'facets' => [
                Facet::fromArray([
                    'slug' => 'product_cat',
                    'label' => __('Category', 'sieve'),
                    'type' => 'checkbox',
                    'source' => 'tax:product_cat',
                ])->toArray(),
                Facet::fromArray([
                    'slug' => 'price',
                    'label' => __('Price', 'sieve'),
                    'type' => 'range_slider',
                    'source' => 'price',
                ])->toArray(),
                Facet::fromArray([
                    'slug' => 'stock',
                    'label' => __('Availability', 'sieve'),
                    'type' => 'checkbox',
                    'source' => 'stock',
                ])->toArray(),
                Facet::fromArray([
                    'slug' => 'on_sale',
                    'label' => __('On sale', 'sieve'),
                    'type' => 'checkbox',
                    'source' => 'on_sale',
                ])->toArray(),
            ],
            'per_page' => 12,
            'columns' => 3,
            'appearance' => $this->appearance->defaults(),
        ];
    }
}
