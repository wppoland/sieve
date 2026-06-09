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
        flush_rewrite_rules();
    }
}
