<?php

declare(strict_types=1);

namespace Sieve;

defined('ABSPATH') || exit;

use Sieve\Contract\HasHooks;

/**
 * Main plugin orchestrator: wires the DI container, runs migrations, registers
 * the text domain, and boots every HasHooks service listed in config/hooks.php.
 */
final class Plugin
{
    private static ?Plugin $instance = null;

    private Container $container;

    private bool $booted = false;

    private function __construct()
    {
        $this->container = new Container();
    }

    public static function instance(): Plugin
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }
        $this->booted = true;

        // Register service factories.
        (require PLUGIN_DIR . '/config/services.php')($this->container);

        // Run any pending DB migrations.
        $this->container->get(Migrator::class)->run();

        // Boot hook subscribers in declared order.
        /** @var array<class-string<HasHooks>> $hooks */
        $hooks = require PLUGIN_DIR . '/config/hooks.php';
        foreach ($hooks as $hookClass) {
            $service = $this->container->get($hookClass);
            if ($service instanceof HasHooks) {
                $service->registerHooks();
            }
        }

        /**
         * Fires after Sieve has booted. The PRO plugin extends Sieve here.
         *
         * @param Plugin $plugin The booted plugin instance.
         */
        do_action('sieve/booted', $this);
    }
}
