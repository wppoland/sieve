<?php
/**
 * Elementor widget: Product Filter.
 *
 * A thin wrapper around the [sieve] shortcode so the complete faceted filter
 * (facets, results grid, sorting, active-filter chips and pagination) can be
 * placed with the Elementor editor. Kept deliberately minimal (renders the
 * shortcode) so a future migration to Elementor v4 atomic widgets is localized
 * to this class. Loaded only from the `elementor/widgets/register` hook, so the
 * `\Elementor\Widget_Base` base class is guaranteed to exist here.
 *
 * @package Sieve\Elementor
 */

declare(strict_types=1);

namespace Sieve\Elementor;

defined('ABSPATH') || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * Product Filter Elementor widget.
 */
final class FilterWidget extends Widget_Base
{
    /**
     * Widget machine name (matches the shortcode tag).
     */
    public function get_name(): string
    {
        return 'sieve';
    }

    /**
     * Widget label shown in the editor.
     */
    public function get_title(): string
    {
        return esc_html__('Product Filter', 'sieve');
    }

    /**
     * Editor panel icon.
     */
    public function get_icon(): string
    {
        return 'eicon-filter';
    }

    /**
     * Editor panel categories.
     *
     * @return string[]
     */
    public function get_categories(): array
    {
        return ['woocommerce-elements', 'general'];
    }

    /**
     * Search keywords in the editor.
     *
     * @return string[]
     */
    public function get_keywords(): array
    {
        return ['sieve', 'filter', 'facet', 'faceted', 'product', 'woocommerce'];
    }

    /**
     * Register the editor controls.
     */
    protected function register_controls(): void
    {
        $this->start_controls_section(
            'content',
            ['label' => esc_html__('Product filter', 'sieve')]
        );

        $this->add_control(
            'notice',
            [
                'type'            => Controls_Manager::RAW_HTML,
                'raw'             => esc_html__('This widget renders the full Sieve faceted filter with its results grid. Configure facets and appearance under WooCommerce → Sieve.', 'sieve'),
                'content_classes' => 'elementor-descriptor',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the widget on the front end and in the editor preview.
     */
    protected function render(): void
    {
        echo do_shortcode('[sieve]');
    }
}
