<?php

declare(strict_types=1);

namespace Sieve\Migration;

defined('ABSPATH') || exit;

/**
 * Creates the Sieve index table. Filtered queries read this pre-built index
 * instead of running live meta_query / tax_query joins, which keeps large
 * catalogs fast (and is the core Web Vitals strategy).
 */
final class Migration_0_1_0
{
    public static function migrate(): void
    {
        global $wpdb;

        $table = $wpdb->prefix . 'sieve_index';
        $charsetCollate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // dbDelta builds/updates the schema idempotently.
        dbDelta(
            "CREATE TABLE {$table} (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                object_id BIGINT UNSIGNED NOT NULL,
                facet_slug VARCHAR(100) NOT NULL,
                value VARCHAR(191) NOT NULL DEFAULT '',
                value_num DECIMAL(20,4) NULL DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY facet_value (facet_slug, value),
                KEY facet_num (facet_slug, value_num),
                KEY object_id (object_id)
            ) {$charsetCollate};"
        );
    }
}
