<?php

declare(strict_types=1);

namespace Sieve\Repository;

defined('ABSPATH') || exit;

/**
 * Read/write access to the Sieve index table (wp_sieve_index). Filtered queries
 * resolve matching object IDs from this pre-built index rather than running live
 * meta/tax joins, which keeps large catalogs fast and is the core Web Vitals
 * strategy.
 *
 * Row shape: object_id, facet_slug (index key), value (string), value_num (float).
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
     * @param array<int, array{facet_slug: string, value: string, value_num: float|null}> $rows
     */
    public function reindexObject(int $objectId, array $rows): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->delete($this->table, ['object_id' => $objectId], ['%d']);

        foreach ($rows as $row) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
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

    public function deleteObject(int $objectId): void
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->delete($this->table, ['object_id' => $objectId], ['%d']);
    }

    public function truncate(): void
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("TRUNCATE TABLE {$this->table}");
    }

    public function rowCount(): int
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table}");
    }

    /**
     * Object IDs that match any of the given values for a facet (OR within a facet).
     *
     * @param array<int, string> $values
     * @return array<int, int>
     */
    public function objectsForValues(string $facetSlug, array $values): array
    {
        global $wpdb;

        $values = array_values(array_filter(array_map('strval', $values), static fn ($v): bool => '' !== $v));
        if (empty($values)) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($values), '%s'));
        $params = array_merge([$facetSlug], $values);

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $ids = $wpdb->get_col(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                "SELECT DISTINCT object_id FROM {$this->table} WHERE facet_slug = %s AND value IN ({$placeholders})",
                $params,
            )
        );

        return array_map('intval', $ids);
    }

    /**
     * Object IDs whose numeric value for a facet falls within [min, max].
     *
     * @return array<int, int>
     */
    public function objectsForRange(string $facetSlug, ?float $min, ?float $max): array
    {
        global $wpdb;

        $sql = "SELECT DISTINCT object_id FROM {$this->table} WHERE facet_slug = %s AND value_num IS NOT NULL";
        $params = [$facetSlug];

        if (null !== $min) {
            $sql .= ' AND value_num >= %f';
            $params[] = $min;
        }
        if (null !== $max) {
            $sql .= ' AND value_num <= %f';
            $params[] = $max;
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $ids = $wpdb->get_col($wpdb->prepare($sql, $params));

        return array_map('intval', $ids);
    }

    /**
     * Count of distinct objects per value for a facet, optionally restricted to a
     * candidate object set (used for dependent facet counts).
     *
     * @param array<int, int>|null $restrictIds
     * @return array<string, int> value => count
     */
    public function valueCounts(string $facetSlug, ?array $restrictIds = null): array
    {
        global $wpdb;

        $sql = "SELECT value, COUNT(DISTINCT object_id) AS c FROM {$this->table} WHERE facet_slug = %s";
        $params = [$facetSlug];

        if (null !== $restrictIds) {
            if (empty($restrictIds)) {
                return [];
            }
            $placeholders = implode(', ', array_fill(0, count($restrictIds), '%d'));
            $sql .= " AND object_id IN ({$placeholders})";
            $params = array_merge($params, array_map('intval', $restrictIds));
        }

        $sql .= ' GROUP BY value';

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $rows = $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);

        $counts = [];
        foreach ((array) $rows as $row) {
            $counts[(string) $row['value']] = (int) $row['c'];
        }
        return $counts;
    }

    /**
     * Min and max numeric bounds for a facet (e.g. price slider range).
     *
     * @return array{min: float, max: float}
     */
    public function numericBounds(string $facetSlug): array
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $row = $wpdb->get_row(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                "SELECT MIN(value_num) AS lo, MAX(value_num) AS hi FROM {$this->table} WHERE facet_slug = %s AND value_num IS NOT NULL",
                $facetSlug,
            ),
            ARRAY_A
        );

        return [
            'min' => isset($row['lo']) ? (float) $row['lo'] : 0.0,
            'max' => isset($row['hi']) ? (float) $row['hi'] : 0.0,
        ];
    }
}
