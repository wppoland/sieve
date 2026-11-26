<?php
/**
 * existing-checked: src/Shortcode/FilterShortcode.php and src/Hook/BlockHooks.php - both render Sieve's own grid inside a page and never touch the WooCommerce archive loop; src/Hook/FrontendHooks.php only registers assets.
 */

declare(strict_types=1);

namespace Sieve\Hook;

defined('ABSPATH') || exit;

use Sieve\Contract\HasHooks;
use Sieve\Service\FilterEngine;
use Sieve\Service\UrlService;

/**
 * A filter address opened directly (a shared link, a search engine) lists the
 * filtered products in the shop or category archive's own loop, so the page
 * shows what its URL says before any script runs.
 */
final class ArchiveHooks implements HasHooks
{
    public function __construct(
        private readonly FilterEngine $engine,
        private readonly UrlService $url,
    ) {
    }

    public function registerHooks(): void
    {
        add_action('woocommerce_product_query', [$this, 'filterLoop']);
    }

    public function filterLoop(\WP_Query $query): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only public filter state.
        $params = map_deep(wp_unslash($_GET), 'sanitize_text_field');
        $request = $this->url->parse(is_array($params) ? $params : []);

        /**
         * Whether Sieve filter parameters in the URL also filter the archive loop.
         *
         * @param bool $filter Default true.
         */
        if (! $query->is_main_query() || ([] === $request['filters'] && '' === $request['search']) || ! apply_filters('sieve_filter_archive_query', true)) {
            return;
        }

        $ids = $this->engine->resolveObjectIds($request);
        if (null !== $ids) {
            $query->set('post__in', [] === $ids ? [0] : $ids);
        }
    }
}
