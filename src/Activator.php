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

        // Force a fresh index build on (re)activation: clearing the flag makes the
        // next admin load schedule the background backfill (or, where wp-cron is
        // switched off, index its first batch inline), so facets cover products
        // that existed before the plugin was activated. The cursor goes too, so
        // the build starts from the first product rather than resuming a walk
        // that belonged to the previous installation.
        delete_option('sieve_index_ready');
        delete_option('sieve_index_cursor');

        flush_rewrite_rules();
    }
}
