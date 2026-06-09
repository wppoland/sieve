<?php

declare(strict_types=1);

namespace Sieve\Service;

defined('ABSPATH') || exit;

/**
 * Runs the filtered product query and renders the results grid using the active
 * theme's WooCommerce templates, so products look identical to the native shop.
 * Returns the markup plus the result count and pagination for the engine.
 */
final class ResultsRenderer
{
    /**
     * @param array<int, int>|null $ids   Resolved IDs, or null for the full catalog.
     * @return array{html: string, count_text: string, found: int, max_pages: int}
     */
    public function render(?array $ids, string $orderby, int $paged, string $search, int $perPage, int $columns): array
    {
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $perPage,
            'paged' => $paged,
        ];

        if (null !== $ids) {
            // Empty post__in is ignored by WP_Query (returns everything), so force
            // an impossible ID to render the genuine "no results" state.
            $args['post__in'] = empty($ids) ? [0] : $ids;
        }

        if ('' !== $search) {
            $args['s'] = $search;
        }

        $this->applyOrderby($args, $orderby);

        // Hide out-of-catalog / excluded products the way WooCommerce does.
        if (function_exists('WC') && WC()->query instanceof \WC_Query) {
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
            $args['tax_query'] = WC()->query->get_tax_query();
        }

        $query = new \WP_Query($args);

        $html = $this->captureLoop($query, $columns);
        $found = (int) $query->found_posts;

        return [
            'html' => $html,
            'count_text' => $this->countText($found),
            'found' => $found,
            'max_pages' => (int) $query->max_num_pages,
        ];
    }

    private function captureLoop(\WP_Query $query, int $columns): string
    {
        global $wp_query;
        $original = $wp_query;
        $wp_query = $query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

        ob_start();

        if ($query->have_posts()) {
            if (function_exists('wc_set_loop_prop')) {
                wc_set_loop_prop('columns', $columns);
                wc_set_loop_prop('is_paginated', true);
            }

            do_action('woocommerce_before_shop_loop');
            woocommerce_product_loop_start();

            while ($query->have_posts()) {
                $query->the_post();
                wc_get_template_part('content', 'product');
            }

            woocommerce_product_loop_end();
            do_action('woocommerce_after_shop_loop');
        } else {
            wc_get_template('loop/no-products-found.php');
        }

        $wp_query = $original; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        wp_reset_postdata();

        return (string) ob_get_clean();
    }

    /**
     * @param array<string, mixed> $args
     */
    private function applyOrderby(array &$args, string $orderby): void
    {
        switch ($orderby) {
            case 'price':
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'ASC';
                $args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                break;
            case 'price-desc':
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                $args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                break;
            case 'rating':
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                $args['meta_key'] = '_wc_average_rating'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                break;
            case 'popularity':
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                $args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                break;
            case 'date':
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
            default:
                $args['orderby'] = 'menu_order title';
                $args['order'] = 'ASC';
        }
    }

    private function countText(int $found): string
    {
        if (0 === $found) {
            return __('No products found.', 'sieve');
        }

        return sprintf(
            /* translators: %s: number of products. */
            _n('%s product', '%s products', $found, 'sieve'),
            number_format_i18n($found)
        );
    }
}
