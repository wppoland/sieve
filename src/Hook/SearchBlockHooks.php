<?php

declare(strict_types=1);

namespace Sieve\Hook;

defined('ABSPATH') || exit;

use Sieve\Contract\HasHooks;
use Sieve\Service\SearchRenderer;

use const Sieve\PLUGIN_DIR;

/**
 * Registers the dynamic "Sieve Search" block, a thin wrapper over the same
 * SearchRenderer the [sieve_search] shortcode uses. Registered only once its
 * build output exists so a missing build never fatals.
 */
final class SearchBlockHooks implements HasHooks
{
    public function __construct(
        private readonly SearchRenderer $renderer,
        private readonly FrontendHooks $frontend,
    ) {
    }

    public function registerHooks(): void
    {
        add_action('init', [$this, 'register']);
    }

    public function register(): void
    {
        $dir = PLUGIN_DIR . '/build/blocks/search';
        if (! file_exists($dir . '/block.json')) {
            return;
        }

        register_block_type($dir, ['render_callback' => [$this, 'render']]);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function render(array $attributes): string
    {
        $this->frontend->enqueueSearch();

        return $this->renderer->render($attributes);
    }
}
