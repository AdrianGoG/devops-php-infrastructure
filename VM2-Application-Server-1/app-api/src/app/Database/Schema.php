<?php

declare(strict_types=1);

namespace App\Database;

use App\Core\Database;

/**
 * Creates the tables of the registry in the MySQL database of the stack.
 */
class Schema
{
    const TABLES = ['deployments', 'applications', 'servers'];

    /** @var Database */
    private $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * @return list<string> the tables that now exist
     */
    public function create(): array
    {
        foreach ($this->statements() as $sql) {
            $this->database->statement($sql);
        }

        return array_reverse(self::TABLES);
    }

    /**
     * @return list<string> the tables that were dropped
     */
    public function drop(): array
    {
        foreach (self::TABLES as $table) {
            $this->database->statement('DROP TABLE IF EXISTS ' . $table);
        }

        return self::TABLES;
    }

    public function fresh(): void
    {
        $this->drop();
        $this->create();
    }

    /**
     * Indexes are declared inside CREATE TABLE: MySQL has no
     * "CREATE INDEX IF NOT EXISTS", so a separate statement would fail on the
     * second run.
     *
     * @return list<string>
     */
    private function statements(): array
    {
        $options = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        return [
            'CREATE TABLE IF NOT EXISTS servers (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                server_key VARCHAR(16) NOT NULL,
                name VARCHAR(120) NOT NULL,
                role VARCHAR(120) NOT NULL,
                host VARCHAR(64) NULL,
                os VARCHAR(64) NOT NULL,
                is_controller TINYINT(1) NOT NULL DEFAULT 0,
                summary TEXT NOT NULL,
                UNIQUE KEY servers_server_key_unique (server_key)
            ) ' . $options,

            'CREATE TABLE IF NOT EXISTS applications (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                server_key VARCHAR(16) NOT NULL,
                title VARCHAR(120) NOT NULL,
                php_version VARCHAR(16) NOT NULL,
                framework VARCHAR(64) NOT NULL,
                port INT NOT NULL,
                status VARCHAR(16) NOT NULL,
                note TEXT NOT NULL,
                UNIQUE KEY applications_name_unique (name),
                KEY applications_server_key_index (server_key),
                KEY applications_status_index (status)
            ) ' . $options,

            'CREATE TABLE IF NOT EXISTS deployments (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                application VARCHAR(120) NOT NULL,
                build_number INT NULL,
                branch VARCHAR(120) NULL,
                commit_sha VARCHAR(64) NULL,
                result VARCHAR(16) NOT NULL,
                duration_seconds INT NULL,
                deployed_at DATETIME NOT NULL,
                notes TEXT NULL,
                KEY deployments_application_index (application),
                KEY deployments_deployed_at_index (deployed_at)
            ) ' . $options,
        ];
    }
}
