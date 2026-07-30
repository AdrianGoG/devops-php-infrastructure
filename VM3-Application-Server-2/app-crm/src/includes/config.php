<?php

/**
 * app-crm - configuration.
 *
 * Reads src/.env with a five line parser. No Composer, no dotenv package: the
 * fewer dependencies a legacy application has, the fewer things can block its
 * PHP upgrade.
 */

function crm_config($key, $default = null)
{
    static $values = null;

    if ($values === null) {
        $values = array();

        $file = dirname(__DIR__) . '/.env';

        if (is_readable($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);

                if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
                    continue;
                }

                list($name, $value) = explode('=', $line, 2);

                $values[trim($name)] = trim(trim($value), '"\'');
            }
        }
    }

    // A real environment variable always wins over the file, which is how the
    // values arrive inside the container.
    $fromEnv = getenv($key);

    if ($fromEnv !== false && $fromEnv !== '') {
        return $fromEnv;
    }

    return isset($values[$key]) ? $values[$key] : $default;
}

// Errors are logged, never printed into a page - except in local development.
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('log_errors', '1');
ini_set('display_errors', crm_config('APP_DEBUG') === 'true' ? '1' : '0');

date_default_timezone_set('UTC');

// The session has to start before anything is printed, otherwise PHP cannot
// send the session cookie and every request would get a fresh CSRF token.
// This file is included first by every page, which is the only safe place for it.
if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
