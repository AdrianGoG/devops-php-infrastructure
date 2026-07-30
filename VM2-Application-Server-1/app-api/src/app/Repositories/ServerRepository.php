<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * The servers of the infrastructure.
 */
class ServerRepository
{
    /** @var Database */
    private $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        return $this->database->select(
            'SELECT server_key, name, role, host, os, is_controller, summary FROM servers ORDER BY server_key'
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByKey(string $key): ?array
    {
        return $this->database->selectOne(
            'SELECT server_key, name, role, host, os, is_controller, summary FROM servers WHERE server_key = :key',
            ['key' => $key]
        );
    }

    public function count(): int
    {
        return $this->database->scalar('SELECT COUNT(*) FROM servers');
    }
}
