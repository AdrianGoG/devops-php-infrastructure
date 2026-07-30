<?php

/**
 * Measures how long the current request has been running, for the uptime
 * metric exposed to Prometheus.
 *
 * ------------------------------------------------------------------------
 * LEGACY WARNING (see MIGRATION.md, incompatibility #4)
 *
 * This file is the oldest part of the service: a global class with no
 * namespace, autoloaded through a Composer classmap instead of PSR-4, using a
 * PHP 4 style constructor - a method named after the class.
 *
 * That constructor style was deprecated in PHP 7.0 and REMOVED in PHP 8.0,
 * where the method is simply an ordinary method that nobody calls. Nothing
 * crashes, which makes this the most dangerous of the six problems: $startedAt
 * stays null and the uptime metric silently reports 0.
 *
 * Note that the class deliberately has no namespace. A namespaced class never
 * treats a same-name method as a constructor (that has been true since PHP
 * 5.3), so moving this file under App\Support would break it on PHP 7.4 too.
 * ------------------------------------------------------------------------
 */
class LegacyTimer
{
    /** @var float|null */
    private $startedAt = null;

    /**
     * LEGACY: PHP 4 style constructor, no longer called on PHP 8.0+.
     */
    public function LegacyTimer()
    {
        $this->startedAt = microtime(true);
    }

    /**
     * Seconds elapsed since the timer was created.
     *
     * @return float
     */
    public function elapsed()
    {
        if ($this->startedAt === null) {
            return 0.0;
        }

        return round(microtime(true) - $this->startedAt, 4);
    }

    /**
     * Whether the timer was initialised at all.
     *
     * Reported by the health endpoint and as a Prometheus gauge, so the silent
     * failure is visible to the monitoring instead of only being wrong.
     *
     * @return bool
     */
    public function isInitialised()
    {
        return $this->startedAt !== null;
    }
}
