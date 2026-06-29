<?php

declare(strict_types=1);

namespace Sieve\Shortcode;

defined('ABSPATH') || exit;

use Sieve\Contract\HasHooks;
use Sieve\Hook\FrontendHooks;
use Sieve\Service\SearchRenderer;

/**
 * The [sieve_search] shortcode renders a predictive product search box with an
 * instant typeahead dropdown. Self-contained and zero-config; degrades to the
 * native WooCommerce product search when JavaScript is unavailable.
 */
final class SearchShortcode implements HasHooks
{
    public function __construct(
        private readonly SearchRenderer $renderer,
        private readonly FrontendHooks $frontend,
    ) {
    }

    public function registerHooks(): void
    {
        add_shortcode('sieve_search', [$this, 'render']);
    }

    /**
     * @param array<string, mixed>|string $atts
     */
    public function render(array|string $atts = []): string
    {
        $this->frontend->enqueueSearch();

        return $this->renderer->render(is_array($atts) ? $atts : []);
    }
}
