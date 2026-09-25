<?php

declare(strict_types=1);

namespace Sieve;

defined('ABSPATH') || exit;

/**
 * Runs on plugin deactivation. Data is preserved; only transient state is
 * cleared. Full data removal happens via uninstall, not deactivation.
 */
final class Deactivator
{
    public static function deactivate(): void
    {
        wp_clear_scheduled_hook(\Sieve\Hook\IndexerHooks::BACKFILL_HOOK);

        flush_rewrite_rules();
    }
}
