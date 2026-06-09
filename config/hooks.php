<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Sieve\Hook\AdminHooks;
use Sieve\Hook\BlockHooks;
use Sieve\Hook\FrontendHooks;
use Sieve\Hook\IndexerHooks;
use Sieve\Hook\RestHooks;
use Sieve\Shortcode\FilterShortcode;

/**
 * Hook subscribers booted in order by Plugin::boot(). Each must implement
 * Sieve\Contract\HasHooks.
 */
return [
    AdminHooks::class,
    RestHooks::class,
    FrontendHooks::class,
    IndexerHooks::class,
    FilterShortcode::class,
    BlockHooks::class,
];
