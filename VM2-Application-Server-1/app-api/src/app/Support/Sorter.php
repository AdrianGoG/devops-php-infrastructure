<?php

namespace App\Support;

/**
 * Sorting helper for the server listing.
 *
 * ------------------------------------------------------------------------
 * LEGACY WARNING (see MIGRATION.md, incompatibility #2)
 *
 * byField() builds its comparison callback with create_function(), which was
 * deprecated in PHP 7.2 and REMOVED in PHP 8.0. On PHP 8 the call raises
 * "Call to undefined function create_function()" and GET /api/servers answers
 * with HTTP 500.
 * ------------------------------------------------------------------------
 */
class Sorter
{
    /**
     * Sort a list of rows by one of their columns.
     *
     * @param array $rows
     * @param string $field
     * @return array
     */
    public static function byField(array $rows, $field)
    {
        // LEGACY: create_function() was removed in PHP 8.0.
        $comparator = create_function(
            '$a, $b',
            'return strcmp((string) $a["' . $field . '"], (string) $b["' . $field . '"]);'
        );

        usort($rows, $comparator);

        // MIGRATION FIX #2
        // usort($rows, function (array $a, array $b) use ($field) {
        //     return strcmp((string) $a[$field], (string) $b[$field]);
        // });

        return $rows;
    }

    /**
     * Group rows by the value of one of their columns.
     *
     * @param array $rows
     * @param string $field
     * @return array
     */
    public static function groupBy(array $rows, $field)
    {
        $grouped = array();

        foreach ($rows as $row) {
            if (!isset($row[$field])) {
                continue;
            }

            $key = (string) $row[$field];

            if (!isset($grouped[$key])) {
                $grouped[$key] = array();
            }

            $grouped[$key][] = $row;
        }

        return $grouped;
    }
}
