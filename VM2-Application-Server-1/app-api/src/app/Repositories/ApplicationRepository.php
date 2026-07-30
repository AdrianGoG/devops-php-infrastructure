<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * The nine PHP applications hosted by the infrastructure.
 */
class ApplicationRepository
{
    const COLUMNS = 'name, server_key, title, php_version, framework, port, status, note';

    /** @var Database */
    private $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * @param array<string, string> $filters server_key, status, php_version
     * @return list<array<string, mixed>>
     */
    public function all(array $filters = []): array
    {
        $sql = 'SELECT ' . self::COLUMNS . ' FROM applications';

        $conditions = [];
        $bindings = [];

        foreach (['server_key', 'status', 'php_version'] as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $conditions[] = $column . ' = :' . $column;
                $bindings[$column] = $filters[$column];
            }
        }

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY server_key, name';

        return $this->database->select($sql, $bindings);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByName(string $name): ?array
    {
        return $this->database->selectOne(
            'SELECT ' . self::COLUMNS . ' FROM applications WHERE name = :name',
            ['name' => $name]
        );
    }

    public function count(): int
    {
        return $this->database->scalar('SELECT COUNT(*) FROM applications');
    }

    /**
     * How many applications are in each state, for the metrics endpoint.
     *
     * @return array<string, int>
     */
    public function countByStatus(): array
    {
        $rows = $this->database->select('SELECT status, COUNT(*) AS total FROM applications GROUP BY status');

        $totals = [];

        foreach ($rows as $row) {
            $totals[(string) $row['status']] = (int) $row['total'];
        }

        return $totals;
    }
}
