<?php
/**
 * Uninstall cleanup for Sieve.
 *
 * Sieve had no uninstall routine at all, so deleting it left the whole facet
 * index table, three options and a per-user banner flag in the database
 * permanently, on every site that ever tried it.
 *
 * Everything here is Sieve's own. The index is derived data, rebuilt from the
 * catalogue whenever the plugin is installed again, so dropping it destroys
 * nothing a merchant typed.
 *
 * Multisite-aware: the table and options are per site, the user meta is global.
 *
 * @package Sieve
 */

declare(strict_types=1);

defined('WP_UNINSTALL_PLUGIN') || exit;

/**
 * Remove Sieve's per-site data.
 */
function sieve_uninstall_cleanup(): void
{
    global $wpdb;

    delete_option('sieve_settings');
    delete_option('sieve_schema_version');
    delete_option('sieve_index_ready');
    delete_transient('sieve_indexing_lock');

    // The index is Sieve's own table, created by its migration.
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'sieve_index');
}

if (is_multisite()) {
    $sieve_site_ids = get_sites(['fields' => 'ids', 'number' => 0]);

    foreach ($sieve_site_ids as $sieve_site_id) {
        switch_to_blog((int) $sieve_site_id);
        sieve_uninstall_cleanup();
        restore_current_blog();
    }

    unset($sieve_site_ids, $sieve_site_id);
} else {
    sieve_uninstall_cleanup();
}

// User meta is global rather than per site, so it is removed once, outside the
// loop, with delete_metadata's $delete_all.
delete_metadata('user', 0, 'sieve_pro_banner_dismissed', '', true);
