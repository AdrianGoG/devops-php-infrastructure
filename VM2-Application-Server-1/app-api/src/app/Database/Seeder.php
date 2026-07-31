<?php

declare(strict_types=1);

namespace App\Database;

use App\Core\Database;

/**
 * Seeds the registry with the real inventory of the project.
 *
 * The values mirror VM1-Jenkins-Ansible-Git/ansible/inventory.ini and the
 * config/project.php of app-company-website, so the API, the presentation site
 * and the Ansible inventory describe the same infrastructure.
 */
class Seeder
{
    /** @var Database */
    private $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * @return array<string, int> how many rows were inserted per table
     */
    public function run(): array
    {
        $this->truncate();

        foreach (self::servers() as $server) {
            $this->database->insert('servers', $server);
        }

        foreach (self::applications() as $application) {
            $this->database->insert('applications', $application);
        }

        foreach (self::deployments() as $deployment) {
            $this->database->insert('deployments', $deployment);
        }

        return [
            'servers' => count(self::servers()),
            'applications' => count(self::applications()),
            'deployments' => count(self::deployments()),
        ];
    }

    private function truncate(): void
    {
        // TRUNCATE also resets AUTO_INCREMENT, so a reseeded database always
        // produces the same identifiers - which keeps the tests deterministic.
        foreach (['deployments', 'applications', 'servers'] as $table) {
            $this->database->statement('TRUNCATE TABLE ' . $table);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function servers(): array
    {
        return [
            [
                'server_key' => 'vm1',
                'name' => 'VM1 - Control Node',
                'role' => 'CI/CD & automation',
                'host' => null,
                'os' => 'Ubuntu Server 24.04 LTS',
                'is_controller' => 1,
                'summary' => 'Runs Jenkins, owns the Ansible playbooks and holds the SSH keys to the application servers.',
            ],
            [
                'server_key' => 'vm2',
                'name' => 'VM2 - Application Server 1',
                'role' => 'Web server & applications',
                'host' => '192.168.0.169',
                'os' => 'Ubuntu Server 24.04 LTS',
                'is_controller' => 0,
                'summary' => 'Hosts the presentation site, the user dashboard and this API.',
            ],
            [
                'server_key' => 'vm3',
                'name' => 'VM3 - Application Server 2',
                'role' => 'Web server & applications',
                'host' => '192.168.0.159',
                'os' => 'Ubuntu Server 24.04 LTS',
                'is_controller' => 0,
                'summary' => 'Hosts the business applications: CRM, inventory and the ticketing system.',
            ],
            [
                'server_key' => 'vm4',
                'name' => 'VM4 - Application Server 3',
                'role' => 'Web server & applications',
                'host' => '192.168.0.125',
                'os' => 'Ubuntu Server 24.04 LTS',
                'is_controller' => 0,
                'summary' => 'Hosts the public blog, the file manager and the monitoring application. All three run Laravel 13 on a runtime one version too low, so they stay down until the PHP upgrade.',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function applications(): array
    {
        return [
            [
                'name' => 'app-company-website',
                'server_key' => 'vm2',
                'title' => 'Presentation site',
                'php_version' => '8.3',
                'framework' => 'Laravel 13',
                'port' => 8081,
                'status' => 'ok',
                'note' => 'The reference application of the project.',
            ],
            [
                'name' => 'app-user-dashboard',
                'server_key' => 'vm2',
                'title' => 'User dashboard',
                'php_version' => '8.2',
                'framework' => 'Laravel + Breeze',
                'port' => 8082,
                'status' => 'pending',
                'note' => 'Authentication and a management panel; consumes this API.',
            ],
            [
                'name' => 'app-api',
                'server_key' => 'vm2',
                'title' => 'Infrastructure registry API',
                'php_version' => '7.4',
                'framework' => 'Plain PHP',
                'port' => 8083,
                'status' => 'legacy',
                'note' => 'Contains constructs removed in PHP 8; see MIGRATION.md.',
            ],
            [
                'name' => 'app-crm',
                'server_key' => 'vm3',
                'title' => 'CRM',
                'php_version' => '7.4',
                'framework' => 'Legacy PHP',
                'port' => 8081,
                'status' => 'legacy',
                'note' => 'Uses syntax that PHP 8 rejects.',
            ],
            [
                'name' => 'app-inventory',
                'server_key' => 'vm3',
                'title' => 'Inventory management',
                'php_version' => '8.0',
                'framework' => 'PHP + Composer',
                'port' => 8082,
                'status' => 'legacy',
                'note' => 'Dependencies pinned to old versions.',
            ],
            [
                'name' => 'app-ticket-system',
                'server_key' => 'vm3',
                'title' => 'Ticketing system',
                'php_version' => '8.1',
                'framework' => 'PHP + Composer',
                'port' => 8083,
                'status' => 'pending',
                'note' => 'Compatible today, scheduled for alignment.',
            ],
            [
                'name' => 'app-blog',
                'server_key' => 'vm4',
                'title' => 'Public blog',
                'php_version' => '8.2',
                'framework' => 'Laravel 13',
                'port' => 8081,
                'status' => 'blocked',
                'note' => 'Laravel 13 requires PHP 8.3: down until the runtime is upgraded.',
            ],
            [
                'name' => 'app-file-manager',
                'server_key' => 'vm4',
                'title' => 'File manager',
                'php_version' => '8.2',
                'framework' => 'Laravel 13',
                'port' => 8082,
                'status' => 'blocked',
                'note' => 'Uploads persisted through a Docker volume. Same version mismatch.',
            ],
            [
                'name' => 'app-monitor',
                'server_key' => 'vm4',
                'title' => 'Monitoring',
                'php_version' => '8.2',
                'framework' => 'Laravel 13',
                'port' => 8083,
                'status' => 'blocked',
                'note' => 'Probes the estate and exposes it to Prometheus; Kubernetes candidate.',
            ],
        ];
    }

    /**
     * A short deployment history, as the pipeline would have written it.
     *
     * @return list<array<string, mixed>>
     */
    public static function deployments(): array
    {
        return [
            [
                'application' => 'app-company-website',
                'build_number' => 126,
                'branch' => 'development',
                'commit_sha' => '9025a3612f4c8b71d0a4e5f6c7b8a9d0e1f2a3b4',
                'result' => 'success',
                'duration_seconds' => 118,
                'deployed_at' => '2026-07-28 09:14:02',
                'notes' => 'Blade views of the presentation site.',
            ],
            [
                'application' => 'app-company-website',
                'build_number' => 127,
                'branch' => 'development',
                'commit_sha' => '14d64751a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d7',
                'result' => 'failed',
                'duration_seconds' => 41,
                'deployed_at' => '2026-07-29 11:02:47',
                'notes' => 'PHPUnit stage failed: 1 assertion on the health contract.',
            ],
            [
                'application' => 'app-company-website',
                'build_number' => 128,
                'branch' => 'main',
                'commit_sha' => 'c3f1a09b8d7e6f5a4b3c2d1e0f9a8b7c6d5e4f3a',
                'result' => 'success',
                'duration_seconds' => 102,
                'deployed_at' => '2026-07-30 08:41:19',
                'notes' => 'Merged into main after the review.',
            ],
            [
                'application' => 'app-api',
                'build_number' => 41,
                'branch' => 'development',
                'commit_sha' => 'b7d2e8f1a0c9b8a7d6e5f4a3b2c1d0e9f8a7b6c5',
                'result' => 'success',
                'duration_seconds' => 67,
                'deployed_at' => '2026-07-30 09:57:33',
                'notes' => 'Registry endpoints on PHP 7.4.',
            ],
            [
                'application' => 'app-crm',
                'build_number' => 18,
                'branch' => 'development',
                'commit_sha' => 'f4a5b6c7d8e9f0a1b2c3d4e5f6a7b8c9d0e1f2a3',
                'result' => 'rolled_back',
                'duration_seconds' => 156,
                'deployed_at' => '2026-07-26 16:22:05',
                'notes' => 'Smoke test returned 503 after the PHP 8.3 upgrade; rolled back to 7.4.',
            ],
            [
                'application' => 'app-inventory',
                'build_number' => 12,
                'branch' => 'main',
                'commit_sha' => 'a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0',
                'result' => 'success',
                'duration_seconds' => 88,
                'deployed_at' => '2026-07-24 13:05:41',
                'notes' => null,
            ],
            [
                'application' => 'app-blog',
                'build_number' => 7,
                'branch' => 'development',
                'commit_sha' => 'd0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9',
                'result' => 'failed',
                'duration_seconds' => 33,
                'deployed_at' => '2026-07-27 10:48:12',
                'notes' => 'Structure validation stage: docker-compose.yml missing.',
            ],
            [
                'application' => 'app-monitor',
                'build_number' => 22,
                'branch' => 'main',
                'commit_sha' => 'e9f8a7b6c5d4e3f2a1b0c9d8e7f6a5b4c3d2e1f0',
                'result' => 'success',
                'duration_seconds' => 74,
                'deployed_at' => '2026-07-29 15:31:58',
                'notes' => 'Prometheus exporter enabled.',
            ],
        ];
    }
}
