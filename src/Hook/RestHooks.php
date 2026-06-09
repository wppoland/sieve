<?php

declare(strict_types=1);

namespace Sieve\Hook;

defined('ABSPATH') || exit;

use Sieve\Contract\HasHooks;
use Sieve\Rest\AdminController;
use Sieve\Rest\FilterController;

/**
 * Registers all REST routes on rest_api_init.
 */
final class RestHooks implements HasHooks
{
    public function __construct(
        private readonly FilterController $filter,
        private readonly AdminController $admin,
    ) {
    }

    public function registerHooks(): void
    {
        add_action('rest_api_init', function (): void {
            $this->filter->registerRoutes();
            $this->admin->registerRoutes();
        });
    }
}
