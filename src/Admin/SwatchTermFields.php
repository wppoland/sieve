<?php

declare(strict_types=1);

namespace Sieve\Admin;

defined('ABSPATH') || exit;

use Sieve\Contract\HasHooks;

/**
 * Colour and image fields on product attribute terms.
 *
 * The swatch facet reads `sieve_swatch_color` and `sieve_swatch_image` off the
 * attribute term, and nothing wrote either of them: the colour survived on the
 * name-guess fallback, the image swatch could never show a picture at all.
 * These are the fields that set them, on the term screens WooCommerce already
 * provides for every attribute (Products, Attributes, Configure terms).
 */
final class SwatchTermFields implements HasHooks
{
    public const COLOR_META = 'sieve_swatch_color';
    public const IMAGE_META = 'sieve_swatch_image';

    private const NONCE = 'sieve_swatch_term';

    public function registerHooks(): void
    {
        // Attribute taxonomies only exist once WooCommerce has registered them,
        // so bind per taxonomy on admin_init rather than at plugin boot.
        add_action('admin_init', [$this, 'bindTaxonomies']);
    }

    public function bindTaxonomies(): void
    {
        if (! function_exists('wc_get_attribute_taxonomy_names')) {
            return;
        }

        foreach (wc_get_attribute_taxonomy_names() as $taxonomy) {
            add_action($taxonomy . '_add_form_fields', [$this, 'renderAddFields']);
            add_action($taxonomy . '_edit_form_fields', [$this, 'renderEditFields']);
            add_action('created_' . $taxonomy, [$this, 'save']);
            add_action('edited_' . $taxonomy, [$this, 'save']);
        }
    }

    public function renderAddFields(): void
    {
        wp_nonce_field(self::NONCE, self::NONCE);

        printf(
            '<div class="form-field"><label for="sieve_swatch_color">%1$s</label>'
                . '<input type="text" name="sieve_swatch_color" id="sieve_swatch_color" value="" placeholder="#336699">'
                . '<p class="description">%2$s</p></div>',
            esc_html__('Swatch colour', 'sieve'),
            esc_html__('Hex colour used by colour swatch facets, for example #336699. Leave empty to guess it from the term name.', 'sieve'),
        );

        printf(
            '<div class="form-field"><label for="sieve_swatch_image">%1$s</label>'
                . '<input type="text" name="sieve_swatch_image" id="sieve_swatch_image" value="">'
                . '<p class="description">%2$s</p></div>',
            esc_html__('Swatch image', 'sieve'),
            esc_html__('Media library attachment ID, or a full image URL. Used by image swatch facets and shown instead of the colour.', 'sieve'),
        );
    }

    /**
     * @param \WP_Term|object $term
     */
    public function renderEditFields($term): void
    {
        $termId = isset($term->term_id) ? (int) $term->term_id : 0;
        $color  = (string) get_term_meta($termId, self::COLOR_META, true);
        $image  = (string) get_term_meta($termId, self::IMAGE_META, true);

        wp_nonce_field(self::NONCE, self::NONCE);

        printf(
            '<tr class="form-field"><th scope="row"><label for="sieve_swatch_color">%1$s</label></th>'
                . '<td><input type="text" name="sieve_swatch_color" id="sieve_swatch_color" value="%2$s" placeholder="#336699">'
                . '<p class="description">%3$s</p></td></tr>',
            esc_html__('Swatch colour', 'sieve'),
            esc_attr($color),
            esc_html__('Hex colour used by colour swatch facets, for example #336699. Leave empty to guess it from the term name.', 'sieve'),
        );

        printf(
            '<tr class="form-field"><th scope="row"><label for="sieve_swatch_image">%1$s</label></th>'
                . '<td><input type="text" name="sieve_swatch_image" id="sieve_swatch_image" value="%2$s">'
                . '<p class="description">%3$s</p></td></tr>',
            esc_html__('Swatch image', 'sieve'),
            esc_attr($image),
            esc_html__('Media library attachment ID, or a full image URL. Used by image swatch facets and shown instead of the colour.', 'sieve'),
        );
    }

    public function save(int $termId): void
    {
        if (! current_user_can('manage_product_terms')) {
            return;
        }

        $nonce = isset($_POST[self::NONCE]) ? sanitize_text_field(wp_unslash((string) $_POST[self::NONCE])) : '';

        if ('' === $nonce || ! wp_verify_nonce($nonce, self::NONCE)) {
            return;
        }

        if (isset($_POST['sieve_swatch_color'])) {
            $raw = sanitize_text_field(wp_unslash((string) $_POST['sieve_swatch_color']));
            // Only a plain hex colour is stored. The value lands in a style
            // attribute on the storefront, so anything else is dropped rather
            // than escaped, which is also what the renderer expects.
            $color = 1 === preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $raw) ? $raw : '';

            if ('' === $color) {
                delete_term_meta($termId, self::COLOR_META);
            } else {
                update_term_meta($termId, self::COLOR_META, $color);
            }
        }

        if (isset($_POST['sieve_swatch_image'])) {
            $raw = sanitize_text_field(wp_unslash((string) $_POST['sieve_swatch_image']));
            $image = is_numeric($raw) ? (string) absint($raw) : esc_url_raw($raw);

            if ('' === $image || '0' === $image) {
                delete_term_meta($termId, self::IMAGE_META);
            } else {
                update_term_meta($termId, self::IMAGE_META, $image);
            }
        }
    }
}
