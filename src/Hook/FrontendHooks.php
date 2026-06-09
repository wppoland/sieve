<?php

declare(strict_types=1);

namespace Sieve\Hook;

defined('ABSPATH') || exit;

use Sieve\Contract\HasHooks;

use const Sieve\PLUGIN_DIR;
use const Sieve\PLUGIN_FILE;
use const Sieve\VERSION;

/**
 * Registers the frontend filter script and styles. Assets are registered up front
 * but only enqueued when a Sieve shortcode or block actually renders, keeping
 * pages that do not use Sieve free of its payload (a Core Web Vitals win).
 */
final class FrontendHooks implements HasHooks
{
    private bool $registered = false;

    private bool $searchRegistered = false;

    public function registerHooks(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'register']);
        add_action('wp_enqueue_scripts', [$this, 'registerSearch']);
    }

    public function register(): void
    {
        if ($this->registered) {
            return;
        }
        $this->registered = true;

        $asset = $this->asset('frontend-filter');

        wp_register_script(
            'sieve-frontend',
            plugins_url('build/frontend-filter.js', PLUGIN_FILE),
            $asset['dependencies'],
            $asset['version'],
            true,
        );

        wp_register_style(
            'sieve-frontend',
            plugins_url('build/frontend-filter.css', PLUGIN_FILE),
            [],
            $asset['version'],
        );

        wp_localize_script('sieve-frontend', 'sieveData', [
            'restUrl' => esc_url_raw(rest_url('sieve/v1/filter')),
            'nonce' => wp_create_nonce('wp_rest'),
            'prefix' => 'sf_',
        ]);
    }

    /**
     * Registers the predictive search bundle. Kept separate from the filter
     * bundle so a page that only uses [sieve_search] never loads the filter
     * engine (a Core Web Vitals win).
     */
    public function registerSearch(): void
    {
        if ($this->searchRegistered) {
            return;
        }
        $this->searchRegistered = true;

        $asset = $this->asset('frontend-suggest');

        wp_register_script(
            'sieve-search',
            plugins_url('build/frontend-suggest.js', PLUGIN_FILE),
            $asset['dependencies'],
            $asset['version'],
            true,
        );

        wp_register_style(
            'sieve-search',
            plugins_url('build/frontend-suggest.css', PLUGIN_FILE),
            [],
            $asset['version'],
        );

        wp_localize_script('sieve-search', 'sieveSearchData', [
            'restUrl' => esc_url_raw(rest_url('sieve/v1/suggest')),
            'nonce' => wp_create_nonce('wp_rest'),
            'i18n' => [
                'noResults' => __('No products found.', 'sieve'),
                'viewAll' => __('View all results', 'sieve'),
                'searching' => __('Searching…', 'sieve'),
            ],
        ]);
    }

    /**
     * Enqueue the registered assets. Called from the shortcode / block at render
     * time. Ensures registration has run even outside the wp_enqueue_scripts hook.
     */
    public function enqueue(): void
    {
        if (! $this->registered) {
            $this->register();
        }
        wp_enqueue_script('sieve-frontend');
        wp_enqueue_style('sieve-frontend');
    }

    /**
     * Enqueue the predictive search assets. Called from the search shortcode /
     * block at render time.
     */
    public function enqueueSearch(): void
    {
        if (! $this->searchRegistered) {
            $this->registerSearch();
        }
        wp_enqueue_script('sieve-search');
        wp_enqueue_style('sieve-search');
    }

    /**
     * @return array{dependencies: array<int, string>, version: string}
     */
    private function asset(string $name): array
    {
        $file = PLUGIN_DIR . '/build/' . $name . '.asset.php';
        if (is_readable($file)) {
            $data = require $file;
            if (is_array($data)) {
                return [
                    'dependencies' => isset($data['dependencies']) && is_array($data['dependencies']) ? $data['dependencies'] : [],
                    'version' => isset($data['version']) ? (string) $data['version'] : VERSION,
                ];
            }
        }

        return ['dependencies' => [], 'version' => VERSION];
    }
}
