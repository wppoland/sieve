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
        flush_rewrite_rules();
    }
}
