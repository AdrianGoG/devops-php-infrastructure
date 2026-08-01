<?php

namespace App\Support;

/**
 * String helpers used when building the public payload of an application.
 *
 * ------------------------------------------------------------------------
 * LEGACY WARNING (see MIGRATION.md, incompatibility #1)
 *
 * shortCode() accesses string offsets with curly braces ($value{0}). That
 * syntax was deprecated in PHP 7.4 and REMOVED in PHP 8.0, where it is a parse
 * error - which means this whole file stops loading and every endpoint that
 * touches it answers with HTTP 500.
 * ------------------------------------------------------------------------
 *
 * This file has no declare(strict_types=1) on purpose: it predates the
 * convention used by the rest of the code base.
 */
class StringHelper
{
    /**
     * Build a short uppercase code out of an application name.
     *
     * "app-company-website" becomes "ACW".
     *
     * @param string $name
     * @return string
     */
    public static function shortCode($name)
    {
        $code = '';

        foreach (explode('-', $name) as $part) {
            if ($part === '') {
                continue;
            }

            // LEGACY: curly brace string offset - parse error on PHP 8.0+.
            $code .= strtoupper($part{0});

            // MIGRATION FIX #1
            // $code .= strtoupper($part[0]);
        }

        return $code;
    }

    /**
     * Human readable label for an application name.
     *
     * @param string $name
     * @return string
     */
    public static function label($name)
    {
        return ucwords(str_replace('-', ' ', $name));
    }

    /**
     * Major.minor of a PHP version string, used for grouping.
     *
     * @param string $version
     * @return string
     */
    public static function majorMinor($version)
    {
        $parts = explode('.', $version);

        if (count($parts) < 2) {
            return $version;
        }

        return $parts[0] . '.' . $parts[1];
    }
}
