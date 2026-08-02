<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

/**
 * Thin PDO wrapper around the MySQL database of the stack.
 *
 * ------------------------------------------------------------------------
 * LEGACY WARNING (see MIGRATION.md, incompatibility #5)
 *
 * This class never calls setAttribute(PDO::ATTR_ERRMODE, ...). On PHP 7.4 the
 * default error mode is PDO::ERRMODE_SILENT, so a failing statement simply
 * returns false and the code below checks for it. PHP 8.0 changed the default
 * to PDO::ERRMODE_EXCEPTION, which turns those silent failures into uncaught
 * PDOException objects - and a 500 response.
 *
 * The behaviour is kept exactly as the original author wrote it, so the upgrade
 * reproduces the real failure instead of hiding it.
 * ------------------------------------------------------------------------
 */
class Database
{
    /** @var PDO */
    private $pdo;

    /** @var string */
    private $database;

    public function __construct(Config $config)
    {
        $this->database = (string) $config->get('DB_DATABASE', 'app_api');

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            (string) $config->get('DB_HOST', '127.0.0.1'),
            (string) $config->get('DB_PORT', '3306'),
            $this->database
        );

        $this->pdo = new PDO(
            $dsn,
            (string) $config->get('DB_USERNAME', 'api'),
            (string) $config->get('DB_PASSWORD', ''),
            [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Native prepared statements: integer columns come back as
                // integers instead of strings.
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 5,

                // LEGACY: no error mode set, so this relies on the PHP 7
                // default of PDO::ERRMODE_SILENT.

                // MIGRATION FIX #5
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]
        );
    }

    public function database(): string
    {
        return $this->database;
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * @param array<string, mixed> $bindings
     * @return list<array<string, mixed>>
     */
    public function select(string $sql, array $bindings = []): array
    {
        $statement = $this->pdo->prepare($sql);

        // Legacy style: a false return is the only failure signal available
        // under ERRMODE_SILENT.
        if ($statement === false) {
            return [];
        }

        $statement->execute($bindings);

        $rows = $statement->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @param array<string, mixed> $bindings
     * @return array<string, mixed>|null
     */
    public function selectOne(string $sql, array $bindings = []): ?array
    {
        $rows = $this->select($sql, $bindings);

        return isset($rows[0]) ? $rows[0] : null;
    }

    /**
     * @param array<string, mixed> $bindings
     */
    public function scalar(string $sql, array $bindings = [], int $default = 0): int
    {
        $statement = $this->pdo->prepare($sql);

        if ($statement === false) {
            return $default;
        }

        $statement->execute($bindings);

        $value = $statement->fetchColumn();

        return $value === false ? $default : (int) $value;
    }

    /**
     * @param array<string, mixed> $bindings
     */
    public function statement(string $sql, array $bindings = []): bool
    {
        $statement = $this->pdo->prepare($sql);

        if ($statement === false) {
            return false;
        }

        return $statement->execute($bindings);
    }

    /**
     * @param array<string, mixed> $values
     */
    public function insert(string $table, array $values): int
    {
        $columns = array_keys($values);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', array_map(function ($column) {
                return ':' . $column;
            }, $columns))
        );

        $this->statement($sql, $values);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Whether the expected tables exist, used by the health check.
     */
    public function isReady(): bool
    {
        $tables = $this->select(
            'SELECT table_name FROM information_schema.tables
             WHERE table_schema = :schema AND table_name IN (\'servers\', \'applications\', \'deployments\')',
            ['schema' => $this->database]
        );

        return count($tables) === 3;
    }

    /**
     * Aggregated counters kept by an older version of the service in a
     * dedicated table.
     *
     * The table was dropped years ago; on PHP 7.4 the failing query silently
     * returns nothing and the caller falls back to counting rows. Under PHP 8
     * the same query raises an uncaught PDOException.
     *
     * LEGACY: see MIGRATION.md, incompatibility #5.
     *
     * @return array<string, mixed>|null
     */
    public function legacyDeploymentStats(): ?array
    {
        $statement = $this->pdo->query('SELECT total, failures FROM deployment_stats WHERE id = 1');

        if ($statement === false) {
            return null;
        }

        $row = $statement->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * Fail with an actionable message instead of a bare PDO error when the
     * database of the stack is not running.
     */
    public static function assertReachable(Config $config): void
    {
        try {
            new self($config);
        } catch (\PDOException $exception) {
            throw new RuntimeException(sprintf(
                'Cannot reach MySQL at %s:%s (database "%s" as user "%s"): %s. '
                . 'Start the stack with "docker compose up -d mysql" and run "php bin/console fresh".',
                (string) $config->get('DB_HOST', '127.0.0.1'),
                (string) $config->get('DB_PORT', '3306'),
                (string) $config->get('DB_DATABASE', 'app_api'),
                (string) $config->get('DB_USERNAME', 'api'),
                $exception->getMessage()
            ), 0, $exception);
        }
    }
}
