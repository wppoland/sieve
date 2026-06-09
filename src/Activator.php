<?php

declare(strict_types=1);

namespace Sieve;

defined('ABSPATH') || exit;

/**
 * Runs on plugin activation: create/upgrade the schema and flush rewrite rules.
 */
final class Activator
{
    public static function activate(): void
    {
        (new Migrator())->run();

        // Force a fresh initial index on (re)activation: clearing the flag makes
        // the next admin load build the index, so facets work immediately even
        // for products that existed before the plugin was activated.
        delete_option('sieve_index_ready');

        flush_rewrite_rules();
    }
}
