<?php

declare(strict_types=1);

namespace Sieve\Contract;

defined('ABSPATH') || exit;

/**
 * A service that registers its own WordPress hooks. Implementations are listed
 * in config/hooks.php and booted in order by Plugin::boot().
 */
interface HasHooks
{
    public function registerHooks(): void;
}
