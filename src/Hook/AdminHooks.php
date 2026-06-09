<?php

declare(strict_types=1);

namespace Sieve\Hook;

defined('ABSPATH') || exit;

use Sieve\Contract\HasHooks;

/**
 * Registers the Sieve admin menu. The page renders a React mount point; the
 * facet builder SPA is enqueued here once the build assets exist.
 */
final class AdminHooks implements HasHooks
{
    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'registerMenu']);
    }

    public function registerMenu(): void
    {
        add_menu_page(
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
}
