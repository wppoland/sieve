<?php

declare(strict_types=1);

namespace Sieve\Repository;

defined('ABSPATH') || exit;

/**
 * Read/write access to the Sieve index table (wp_sieve_index). Filtered queries
 * resolve matching object IDs from this pre-built index rather than running live
 * meta/tax joins.
 */
final class IndexRepository
{
    private string $table;

    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'sieve_index';
    }

    public function table(): string
    {
        return $this->table;
    }

    /**
     * Replace all index rows for one object with a fresh set.
     *
     * @param array<array{facet_slug:string,value:string,value_num:float|null}> $rows
     */
    public function reindexObject(int $objectId, array $rows): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- custom index table.
        $wpdb->delete($this->table, ['object_id' => $objectId], ['%d']);

        foreach ($rows as $row) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- custom index table.
            $wpdb->insert(
                $this->table,
                [
                    'object_id' => $objectId,
                    'facet_slug' => $row['facet_slug'],
                    'value' => $row['value'],
                    'value_num' => $row['value_num'],
                ],
                ['%d', '%s', '%s', '%f'],
            );
        }
    }

    /**
     * Object IDs that have the given value for a facet.
     *
     * @return array<int>
     */
    public function objectsForValue(string $facetSlug, string $value): array
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
        $ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT object_id FROM {$this->table} WHERE facet_slug = %s AND value = %s",
                $facetSlug,
                $value,
            )
        );

        return array_map('intval', $ids);
    }
}
