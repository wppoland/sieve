<?php

declare(strict_types=1);

namespace Sieve\Shortcode;

defined('ABSPATH') || exit;

use Sieve\Contract\HasHooks;
use Sieve\Hook\FrontendHooks;
use Sieve\Service\FilterEngine;
use Sieve\Service\UrlService;

/**
 * The [sieve] shortcode renders a complete, self-contained faceted filter:
 * facets, a results grid, sorting, active-filter chips and pagination. It works
 * on any page or theme without configuration.
 */
final class FilterShortcode implements HasHooks
{
    public function __construct(
        private readonly FilterEngine $engine,
        private readonly UrlService $url,
        private readonly FrontendHooks $frontend,
    ) {
    }

    public function registerHooks(): void
    {
        add_shortcode('sieve', [$this, 'render']);
    }

    public function render(): string
    {
        $this->frontend->enqueue();

        // Filter state is read-only and public; values are sanitised in UrlService.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $params = wp_unslash($_GET);

        return $this->engine->container($this->url->parse(is_array($params) ? $params : []));
    }
}
