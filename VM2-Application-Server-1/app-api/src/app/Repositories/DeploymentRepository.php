<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * The deployment log written by the Jenkins pipeline.
 */
class DeploymentRepository
{
    const COLUMNS = 'id, application, build_number, branch, commit_sha, result, duration_seconds, deployed_at, notes';

    /** @var Database */
    private $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * @param array<string, string> $filters application, result
     * @return list<array<string, mixed>>
     */
    public function recent(array $filters = [], int $limit = 25): array
    {
        $sql = 'SELECT ' . self::COLUMNS . ' FROM deployments';

        $conditions = [];
        $bindings = [];

        foreach (['application', 'result'] as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $conditions[] = $column . ' = :' . $column;
                $bindings[$column] = $filters[$column];
            }
        }

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        // The limit is interpolated after being cast to int: SQLite and MySQL
        // both refuse a bound parameter in a LIMIT clause with emulation off.
        $sql .= ' ORDER BY deployed_at DESC, id DESC LIMIT ' . max(1, min($limit, 200));

        return array_map([self::class, 'hydrate'], $this->database->select($sql, $bindings));
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): int
    {
        return $this->database->insert('deployments', $attributes);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $row = $this->database->selectOne(
            'SELECT ' . self::COLUMNS . ' FROM deployments WHERE id = :id',
            ['id' => $id]
        );

        return $row === null ? null : self::hydrate($row);
    }

    /**
     * Force the numeric columns to real integers.
     *
     * Native prepared statements already return them as integers, but the cast
     * keeps the JSON contract stable even if PDO::ATTR_EMULATE_PREPARES is ever
     * turned back on.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private static function hydrate(array $row): array
    {
        foreach (['id', 'build_number', 'duration_seconds'] as $column) {
            if (array_key_exists($column, $row)) {
                $row[$column] = $row[$column] === null ? null : (int) $row[$column];
            }
        }

        return $row;
    }

    public function count(): int
    {
        return $this->database->scalar('SELECT COUNT(*) FROM deployments');
    }

    /**
     * @return array<string, int>
     */
    public function countByResult(): array
    {
        $rows = $this->database->select('SELECT result, COUNT(*) AS total FROM deployments GROUP BY result');

        $totals = [];

        foreach ($rows as $row) {
            $totals[(string) $row['result']] = (int) $row['total'];
        }

        return $totals;
    }

    public function averageDuration(): float
    {
        $rows = $this->database->select('SELECT duration_seconds FROM deployments WHERE duration_seconds IS NOT NULL');

        if ($rows === []) {
            return 0.0;
        }

        $total = 0;

        foreach ($rows as $row) {
            $total += (int) $row['duration_seconds'];
        }

        return round($total / count($rows), 2);
    }
}
