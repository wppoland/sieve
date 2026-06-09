<?php
/**
 * Constants needed by PHPStan to analyse the plugin without bootstrapping WordPress.
 *
 * @package Sieve
 */

declare(strict_types=1);

namespace {
    if (! defined('ABSPATH')) {
        define('ABSPATH', '/tmp/wordpress/');
    }
    // WC_VERSION is provided by the WooCommerce stubs bootstrap file.
}

namespace Sieve {
    if (! defined('Sieve\\VERSION')) {
        define('Sieve\\VERSION', '0.1.0');
    }
    if (! defined('Sieve\\PLUGIN_FILE')) {
        define('Sieve\\PLUGIN_FILE', '/tmp/sieve/sieve.php');
    }
    if (! defined('Sieve\\PLUGIN_DIR')) {
        define('Sieve\\PLUGIN_DIR', '/tmp/sieve');
    }
    if (! defined('Sieve\\MIN_PHP_VERSION')) {
        define('Sieve\\MIN_PHP_VERSION', '8.1.0');
    }
    if (! defined('Sieve\\MIN_WC_VERSION')) {
        define('Sieve\\MIN_WC_VERSION', '8.0.0');
    }
}
