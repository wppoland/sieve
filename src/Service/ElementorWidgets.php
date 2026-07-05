<?php

declare(strict_types=1);

namespace Sieve\Service;

defined('ABSPATH') || exit;

use Sieve\Contract\HasHooks;
use Sieve\Elementor\FilterWidget;
use Sieve\Elementor\SearchWidget;

/**
 * Elementor integration service. Registers the Sieve Elementor widgets.
 *
 * The `elementor/widgets/register` action only fires when Elementor is active,
 * so this service is self-guarding: nothing loads unless Elementor is present.
 */
final class ElementorWidgets implements HasHooks
{
    public function registerHooks(): void
    {
        add_action('elementor/widgets/register', [$this, 'register']);
    }

    /**
     * Register widget instances with Elementor's widgets manager.
     *
     * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
     */
    public function register($widgets_manager): void
    {
        // Loaded here (not autoloaded) so \Elementor\Widget_Base always exists.
        require_once __DIR__ . '/../Elementor/SearchWidget.php';
        require_once __DIR__ . '/../Elementor/FilterWidget.php';

        $widgets_manager->register(new SearchWidget());
        $widgets_manager->register(new FilterWidget());
    }
}
