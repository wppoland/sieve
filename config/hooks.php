<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Sieve\Admin\SwatchTermFields;
use Sieve\Hook\AdminHooks;
use Sieve\Hook\BlockHooks;
use Sieve\Hook\FrontendHooks;
use Sieve\Hook\IndexerHooks;
use Sieve\Hook\RestHooks;
use Sieve\Hook\SearchBlockHooks;
use Sieve\Service\ElementorWidgets;
use Sieve\Shortcode\FilterShortcode;
use Sieve\Shortcode\SearchShortcode;

/**
 * Hook subscribers booted in order by Plugin::boot(). Each must implement
 * Sieve\Contract\HasHooks.
 */
return [
    AdminHooks::class,

    // Colour and image fields on attribute terms. The swatch facet reads both
    // metas, nothing wrote either of them.
    SwatchTermFields::class,
    RestHooks::class,
    FrontendHooks::class,
    IndexerHooks::class,
    FilterShortcode::class,
    BlockHooks::class,
    SearchShortcode::class,
    SearchBlockHooks::class,
    ElementorWidgets::class,
];
