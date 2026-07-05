<?php
/**
 * Elementor widget: Product Search.
 *
 * A thin wrapper around the [sieve_search] shortcode so the predictive search
 * box can be placed with the Elementor editor. Kept deliberately minimal
 * (renders the shortcode) so a future migration to Elementor v4 atomic widgets
 * is localized to this class. Loaded only from the `elementor/widgets/register`
 * hook, so the `\Elementor\Widget_Base` base class is guaranteed to exist here.
 *
 * @package Sieve\Elementor
 */

declare(strict_types=1);

namespace Sieve\Elementor;

defined('ABSPATH') || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * Product Search Elementor widget.
 */
final class SearchWidget extends Widget_Base
{
    /**
     * Widget machine name (matches the shortcode tag).
     */
    public function get_name(): string
    {
        return 'sieve_search';
    }

    /**
     * Widget label shown in the editor.
     */
    public function get_title(): string
    {
        return esc_html__('Product Search', 'sieve');
    }

    /**
     * Editor panel icon.
     */
    public function get_icon(): string
    {
        return 'eicon-search';
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
        return ['sieve', 'search', 'product', 'predictive', 'typeahead', 'woocommerce'];
    }

    /**
     * Register the editor controls.
     */
    protected function register_controls(): void
    {
        $this->start_controls_section(
            'content',
            ['label' => esc_html__('Product search', 'sieve')]
        );

        $this->add_control(
            'placeholder',
            [
                'label'       => esc_html__('Placeholder', 'sieve'),
                'type'        => Controls_Manager::TEXT,
                'default'     => '',
                'description' => esc_html__('Leave empty to use the default placeholder text.', 'sieve'),
            ]
        );

        $this->add_control(
            'button',
            [
                'label'        => esc_html__('Show search button', 'sieve'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'sieve'),
                'label_off'    => esc_html__('No', 'sieve'),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the widget on the front end and in the editor preview.
     */
    protected function render(): void
    {
        $settings    = $this->get_settings_for_display();
        $placeholder = isset($settings['placeholder']) ? (string) $settings['placeholder'] : '';
        $button      = isset($settings['button']) && 'yes' === $settings['button'] ? 'true' : 'false';

        $atts = sprintf(' button="%s"', esc_attr($button));

        if ('' !== $placeholder) {
            $atts .= sprintf(' placeholder="%s"', esc_attr($placeholder));
        }

        echo do_shortcode('[sieve_search' . $atts . ']');
    }
}
