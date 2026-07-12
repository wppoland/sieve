<?php

declare(strict_types=1);

namespace Sieve\Hook;

defined('ABSPATH') || exit;

use Sieve\Admin\ProUpsell;
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

    private ?ProUpsell $upsell = null;

    private function upsell(): ProUpsell
    {
        return $this->upsell ??= new ProUpsell();
    }

    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        add_action('admin_post_' . ProUpsell::ACTION, [$this->upsell(), 'handleDismiss']);
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
        echo '<div class="wrap">';
        $this->upsell()->banner();
        echo '<div id="sieve-admin-root"></div>';
        $this->upsell()->cards();
        echo '</div>';
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
        wp_add_inline_style('wp-components', $this->upsellCss());
        wp_set_script_translations('sieve-admin', 'sieve');

        wp_localize_script('sieve-admin', 'sieveAdmin', [
            'restRoot' => esc_url_raw(rest_url('sieve/v1')),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }

    private function upsellCss(): string
    {
        return '.sieve-pro-banner{display:flex;align-items:center;gap:12px;margin:16px 0;padding:12px 16px;border:1px solid #c3c4c7;border-left:4px solid #2271b1;border-radius:6px;background:#fff}'
            . '.sieve-pro-banner__tag{flex:none;font-size:11px;font-weight:700;letter-spacing:.05em;color:#fff;background:#2271b1;padding:2px 8px;border-radius:3px}'
            . '.sieve-pro-banner__text{margin:0;flex:1;display:flex;flex-wrap:wrap;gap:4px 10px;align-items:baseline}'
            . '.sieve-pro-banner__sub{color:#50575e}.sieve-pro-banner__price{color:#2271b1;font-weight:600}.sieve-pro-banner__cta{flex:none}'
            . '.sieve-pro-banner__dismiss{flex:none;color:#787c82;text-decoration:none;font-size:18px;line-height:1;padding:2px 6px}'
            . '.sieve-pro-cards{margin:24px 0}.sieve-pro-cards__title{font-size:16px;margin:0 0 12px}'
            . '.sieve-pro-cards__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px}'
            . '.sieve-pro-card{position:relative;padding:14px 16px;border:1px solid #dcdcde;border-radius:8px;background:#fff}'
            . '.sieve-pro-card__badge{position:absolute;top:10px;right:10px;font-size:10px;font-weight:700;color:#fff;background:#2271b1;padding:1px 6px;border-radius:3px}'
            . '.sieve-pro-card__title{font-size:14px;margin:0 0 4px;padding-right:36px}.sieve-pro-card__desc{margin:0;font-size:13px;color:#50575e}'
            . '@media(prefers-reduced-motion:reduce){.sieve-pro-banner,.sieve-pro-card{transition:none}}';
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
