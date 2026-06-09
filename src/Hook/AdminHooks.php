<?php

declare(strict_types=1);

namespace Sieve\Hook;

defined('ABSPATH') || exit;

use Sieve\Contract\HasHooks;

use const Sieve\PLUGIN_DIR;
use const Sieve\PLUGIN_FILE;
use const Sieve\VERSION;

/**
 * Registers the Sieve admin menu and mounts the React facet builder SPA.
 */
final class AdminHooks implements HasHooks
{
    private string $hookSuffix = '';

    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    public function registerMenu(): void
    {
        $this->hookSuffix = (string) add_menu_page(
            __('Sieve', 'sieve'),
            __('Sieve', 'sieve'),
            'manage_woocommerce',
            'sieve',
            [$this, 'renderPage'],
            'dashicons-filter',
            56,
        );
    }

    public function renderPage(): void
    {
        echo '<div class="wrap"><div id="sieve-admin-root"></div></div>';
    }

    public function enqueue(string $hookSuffix): void
    {
        if ($hookSuffix !== $this->hookSuffix) {
            return;
        }

        $asset = $this->asset();

        wp_enqueue_script(
            'sieve-admin',
            plugins_url('build/admin.js', PLUGIN_FILE),
            $asset['dependencies'],
            $asset['version'],
            true,
        );

        wp_enqueue_style('wp-components');
        wp_set_script_translations('sieve-admin', 'sieve');

        wp_localize_script('sieve-admin', 'sieveAdmin', [
            'restRoot' => esc_url_raw(rest_url('sieve/v1')),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }

    /**
     * @return array{dependencies: array<int, string>, version: string}
     */
    private function asset(): array
    {
        $file = PLUGIN_DIR . '/build/admin.asset.php';
        if (is_readable($file)) {
            $data = require $file;
            if (is_array($data)) {
                return [
                    'dependencies' => isset($data['dependencies']) && is_array($data['dependencies']) ? $data['dependencies'] : [],
                    'version' => isset($data['version']) ? (string) $data['version'] : VERSION,
                ];
            }
        }

        return ['dependencies' => ['wp-element', 'wp-components', 'wp-api-fetch', 'wp-i18n'], 'version' => VERSION];
    }
}
