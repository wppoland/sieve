<?php

declare(strict_types=1);

namespace Sieve\Hook;

defined('ABSPATH') || exit;

use Sieve\Contract\HasHooks;
use Sieve\Service\FilterEngine;
use Sieve\Service\UrlService;

use const Sieve\PLUGIN_DIR;

/**
 * Registers the dynamic "Sieve Filter" block. The block is a thin wrapper over the
 * same FilterEngine the shortcode uses, registered only once its build output
 * exists so a missing build never fatals.
 */
final class BlockHooks implements HasHooks
{
    public function __construct(
        private readonly FilterEngine $engine,
        private readonly UrlService $url,
        private readonly FrontendHooks $frontend,
    ) {
    }

    public function registerHooks(): void
    {
        add_action('init', [$this, 'register']);
    }

    public function register(): void
    {
        $dir = PLUGIN_DIR . '/build/blocks/filter';
        if (! file_exists($dir . '/block.json')) {
            return;
        }

        register_block_type($dir, ['render_callback' => [$this, 'render']]);
    }

    public function render(): string
    {
        $this->frontend->enqueue();

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $params = wp_unslash($_GET);

        return $this->engine->container($this->url->parse(is_array($params) ? $params : []));
    }
}
