<?php

namespace App\Support;

/**
 * Iteration helpers used by the deployment listing.
 *
 * ------------------------------------------------------------------------
 * LEGACY WARNING (see MIGRATION.md, incompatibility #3)
 *
 * summarise() walks the rows with each(), which was deprecated in PHP 7.2 and
 * REMOVED in PHP 8.0. On PHP 8 it raises "Call to undefined function each()"
 * and GET /api/deployments answers with HTTP 500.
 * ------------------------------------------------------------------------
 */
class Collection
{
    /**
     * Count deployment results per outcome.
     *
     * @param array $rows
     * @param string $field
     * @return array
     */
    public static function summarise(array $rows, $field)
    {
        $totals = array();

        // LEGACY: each() was removed in PHP 8.0.
        // MIGRATION FIX #3
        foreach ($rows as $row) {
        // while (list($index, $row) = each($rows)) {
            unset($index);

            if (!isset($row[$field])) {
                continue;
            }

            $key = (string) $row[$field];

            if (!isset($totals[$key])) {
                $totals[$key] = 0;
            }

            $totals[$key]++;
        }

        return $totals;
    }

    /**
     * Average value of a numeric column.
     *
     * @param array $rows
     * @param string $field
     * @return float
     */
    public static function average(array $rows, $field)
    {
        $values = array();

        foreach ($rows as $row) {
            if (isset($row[$field]) && is_numeric($row[$field])) {
                $values[] = (float) $row[$field];
            }
        }

        if (count($values) === 0) {
            return 0.0;
        }

        return round(array_sum($values) / count($values), 2);
    }
}
