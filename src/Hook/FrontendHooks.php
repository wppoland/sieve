<?php

declare(strict_types=1);

namespace Sieve\Hook;

defined('ABSPATH') || exit;

use Sieve\Contract\HasHooks;
use Sieve\Service\AppearanceService;
use Sieve\Service\Settings;

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
    /**
     * Keyboard-focus and high-contrast rules re-emitted for the "unstyled"
     * preset, where the base stylesheet is not enqueued. Keeps unstyled from
     * regressing accessibility while leaving all visual styling to the theme.
     */
    private const A11Y_SHIM = '.sieve-app :focus-visible,.sieve-search :focus-visible{outline:2px solid Highlight;outline-offset:2px}'
        . '@media (forced-colors:active){.sieve-az__letter.is-active,.sieve-search__item.is-active{outline:2px solid Highlight;outline-offset:-2px}}';

    private bool $registered = false;

    private bool $searchRegistered = false;

    private bool $filterInlined = false;

    private bool $searchInlined = false;

    private bool $a11yRegistered = false;

    public function __construct(
        private readonly Settings $settings,
        private readonly AppearanceService $appearance,
    ) {
    }

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
            // The suggest endpoint, supplied explicitly so the in-grid combobox
            // never has to derive it from the filter URL by string surgery.
            'suggestUrl' => esc_url_raw(rest_url('sieve/v1/suggest')),
            'nonce' => wp_create_nonce('wp_rest'),
            'prefix' => 'sf_',
            'i18n' => [
                /* translators: %d: number of matching options. */
                'optionsCount' => __('%d options', 'sieve'),
                'noOptions' => __('No matching options', 'sieve'),
                'searching' => __('Searching…', 'sieve'),
                /* translators: %d: number of matching products (singular). */
                'oneResult' => __('%d product', 'sieve'),
                /* translators: %d: number of matching products. */
                'manyResults' => __('%d products', 'sieve'),
                'noResults' => __('No results', 'sieve'),
                'suggestionsLabel' => __('Product suggestions', 'sieve'),
            ],
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
                'productsHeading' => __('Products', 'sieve'),
                'categoriesHeading' => __('Categories', 'sieve'),
                'oneResult' => __('1 result found', 'sieve'),
                /* translators: %d: number of search results found. */
                'manyResults' => __('%d results found', 'sieve'),
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

        $appearance = $this->appearance->resolveFrom($this->settings->all());

        // Unstyled: ship the behaviour bundle and a tiny a11y shim only; the base
        // stylesheet is never enqueued so the theme owns every visual rule.
        if ('unstyled' === $appearance['preset']) {
            wp_enqueue_script('sieve-frontend');
            $this->enqueueA11yShim();
            return;
        }

        wp_enqueue_script('sieve-frontend');
        wp_enqueue_style('sieve-frontend');

        // Per-store colour overrides, inlined once per page right after the sheet.
        if (! $this->filterInlined && ! $appearance['isDefault']) {
            $css = $this->appearance->colorBlock('filter', $appearance['colors']);
            if ('' !== $css) {
                wp_add_inline_style('sieve-frontend', $css);
            }
            $this->filterInlined = true;
        }
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

        $appearance = $this->appearance->resolveFrom($this->settings->all());

        if ('unstyled' === $appearance['preset']) {
            wp_enqueue_script('sieve-search');
            $this->enqueueA11yShim();
            return;
        }

        wp_enqueue_script('sieve-search');
        wp_enqueue_style('sieve-search');

        if (! $this->searchInlined && ! $appearance['isDefault']) {
            $css = $this->appearance->colorBlock('search', $appearance['colors']);
            if ('' !== $css) {
                wp_add_inline_style('sieve-search', $css);
            }
            $this->searchInlined = true;
        }
    }

    /**
     * Register (once) and enqueue an inline-only stylesheet carrying just the
     * keyboard-focus and forced-colors rules, so the "unstyled" preset does not
     * regress accessibility when the base sheet is withheld.
     */
    private function enqueueA11yShim(): void
    {
        if (! $this->a11yRegistered) {
            wp_register_style('sieve-a11y', false, [], VERSION);
            wp_add_inline_style('sieve-a11y', self::A11Y_SHIM);
            $this->a11yRegistered = true;
        }
        wp_enqueue_style('sieve-a11y');
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
