<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Sieve\Hook\AdminHooks;

/**
 * Hook subscribers booted in order by Plugin::boot(). Each must implement
 * Sieve\Contract\HasHooks. Add FrontendHooks / FilterHooks / RestHooks here as
 * the MVP lands.
 */
return [
    AdminHooks::class,
];
