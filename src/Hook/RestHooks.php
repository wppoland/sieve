<?php

declare(strict_types=1);

namespace Sieve\Hook;

defined('ABSPATH') || exit;

use Sieve\Contract\HasHooks;
use Sieve\Rest\AdminController;
use Sieve\Rest\FilterController;
use Sieve\Rest\SuggestController;

/**
 * Registers all REST routes on rest_api_init.
 */
final class RestHooks implements HasHooks
{
    public function __construct(
        private readonly FilterController $filter,
        private readonly AdminController $admin,
        private readonly SuggestController $suggest,
    ) {
    }

    public function registerHooks(): void
    {
        add_action('rest_api_init', function (): void {
            $this->filter->registerRoutes();
            $this->admin->registerRoutes();
            $this->suggest->registerRoutes();
        });
    }
}
