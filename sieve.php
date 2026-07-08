<?php

declare(strict_types=1);

/**
 * Plugin Name:       Sieve - Faceted Filter for WooCommerce
 * Plugin URI:        https://plogins.com/sieve/
 * Description:       Fast, accessible faceted filtering for WooCommerce and WordPress. Beautiful facet widgets, AJAX filtering with no page reload, a mobile filter drawer, and Core Web Vitals by design.
 * Version:           1.0.2
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Tested up to:      7.0
 * Author:            WPPoland.com
 * Author URI:        https://wppoland.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sieve
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 *
 * WC requires at least: 8.0
 * WC tested up to:      9.6
 */

namespace Sieve;

defined('ABSPATH') || exit;

const VERSION = '1.0.2';
const PLUGIN_FILE = __FILE__;
const PLUGIN_DIR = __DIR__;
const MIN_PHP_VERSION = '8.1.0';
const MIN_WC_VERSION = '8.0.0';

/**
 * Declare WooCommerce HPOS (Custom Order Tables) + Blocks compatibility.
 */
add_action('before_woocommerce_init', static function (): void {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', PLUGIN_FILE, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', PLUGIN_FILE, true);
    }
});

/**
 * Require PHP 8.1+ before doing anything else.
 */
if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) {
    add_action('admin_notices', static function (): void {
        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            esc_html(sprintf(
                /* translators: 1: Required PHP version, 2: Current PHP version */
                __('Sieve requires PHP %1$s or higher. You are running PHP %2$s.', 'sieve'),
                MIN_PHP_VERSION,
                PHP_VERSION,
            )),
        );
    });
    return;
}

require_once PLUGIN_DIR . '/autoload.php';

/**
 * Boot once WooCommerce is confirmed present and recent enough.
 */
add_action('plugins_loaded', static function (): void {
    if (! defined('WC_VERSION')) {
        add_action('admin_notices', static function (): void {
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html__('Sieve requires WooCommerce to be installed and activated.', 'sieve'),
            );
        });
        return;
    }

    if (version_compare(WC_VERSION, MIN_WC_VERSION, '<')) {
        add_action('admin_notices', static function (): void {
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html(sprintf(
                    /* translators: 1: Required WC version, 2: Current WC version */
                    __('Sieve requires WooCommerce %1$s or higher. You are running WooCommerce %2$s.', 'sieve'),
                    MIN_WC_VERSION,
                    WC_VERSION,
                )),
            );
        });
        return;
    }

    add_action('init', static function (): void {
        Plugin::instance()->boot();
    }, 0);
}, 10);

register_activation_hook(PLUGIN_FILE, static function (): void {
    require_once PLUGIN_DIR . '/autoload.php';
    Activator::activate();
});

register_deactivation_hook(PLUGIN_FILE, static function (): void {
    Deactivator::deactivate();
});
