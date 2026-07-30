<?php

/**
 * app-crm - database connection.
 *
 * PDO with prepared statements everywhere. The application is old fashioned in
 * style, not insecure: no query in this code base concatenates user input.
 */

require_once __DIR__ . '/config.php';

/**
 * The shared PDO connection.
 *
 * @return PDO
 */
function crm_db()
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        crm_config('DB_HOST', '127.0.0.1'),
        crm_config('DB_PORT', '3306'),
        crm_config('DB_NAME', 'crm')
    );

    $pdo = new PDO(
        $dsn,
        crm_config('DB_USER', 'crm'),
        crm_config('DB_PASS', ''),
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        )
    );

    return $pdo;
}

/**
 * Run a SELECT and return every row.
 *
 * @param string $sql
 * @param array $bindings
 * @return array
 */
function crm_select($sql, $bindings = array())
{
    $statement = crm_db()->prepare($sql);
    $statement->execute($bindings);

    return $statement->fetchAll();
}

/**
 * Run a SELECT and return the first row, or null.
 *
 * @param string $sql
 * @param array $bindings
 * @return array|null
 */
function crm_select_one($sql, $bindings = array())
{
    $rows = crm_select($sql, $bindings);

    return isset($rows[0]) ? $rows[0] : null;
}

/**
 * Run an INSERT / UPDATE / DELETE.
 *
 * @param string $sql
 * @param array $bindings
 * @return bool
 */
function crm_execute($sql, $bindings = array())
{
    $statement = crm_db()->prepare($sql);

    return $statement->execute($bindings);
}
