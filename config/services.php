<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Sieve\Container;
use Sieve\Migrator;
use Sieve\Repository\IndexRepository;
use Sieve\Hook\AdminHooks;

/**
 * Service registration. Returns a callable that binds every service into the
 * container. Keep bindings lazy: nothing is constructed until first resolved.
 */
return static function (Container $c): void {
    $c->singleton(Migrator::class, static fn (): Migrator => new Migrator());
    $c->singleton(IndexRepository::class, static fn (): IndexRepository => new IndexRepository());

    // Hook subscribers.
    $c->singleton(AdminHooks::class, static fn (): AdminHooks => new AdminHooks());
};
