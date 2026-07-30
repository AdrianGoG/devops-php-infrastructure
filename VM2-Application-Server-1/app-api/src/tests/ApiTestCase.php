<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Config;
use App\Core\Database;
use App\Core\Kernel;
use App\Core\Request;
use App\Core\Response;
use App\Database\Schema;
use App\Database\Seeder;
use PDOException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Base class for the API tests.
 *
 * Every test runs against a freshly migrated and seeded MySQL database - the
 * same engine the stack uses in production, so nothing is proven against a
 * different driver than the one that actually runs on VM2.
 *
 * The connection settings come from phpunit.xml; the database is dedicated to
 * the test suite (app_api_test) and is recreated on every single test.
 */
abstract class ApiTestCase extends TestCase
{
    const API_KEY = 'test-api-key';

    /**
     * The database is migrated and seeded once per process; every test then runs
     * inside a transaction that is rolled back afterwards.
     *
     * Recreating the schema and reseeding on all 54 tests took forty seconds
     * against MySQL and bought no extra isolation - a rolled back transaction
     * leaves exactly the same state behind.
     *
     * @var bool
     */
    private static $databaseReady = false;

    /** @var Kernel */
    protected $kernel;

    /** @var Database */
    protected $database;

    protected function setUp(): void
    {
        parent::setUp();

        $config = new Config([
            'APP_NAME' => 'Infrastructure Registry API',
            'APP_ENV' => 'testing',
            'APP_DEBUG' => 'true',
            'API_KEY' => self::API_KEY,
            'DB_HOST' => (string) (getenv('DB_HOST') ?: '127.0.0.1'),
            'DB_PORT' => (string) (getenv('DB_PORT') ?: '33063'),
            'DB_DATABASE' => (string) (getenv('DB_DATABASE') ?: 'app_api_test'),
            'DB_USERNAME' => (string) (getenv('DB_USERNAME') ?: 'api'),
            'DB_PASSWORD' => (string) (getenv('DB_PASSWORD') ?: 'secret'),
            'LOG_PATH' => 'storage/logs/testing.log',
            'LOG_LEVEL' => 'error',
        ]);

        $this->kernel = new Kernel($this->basePath(), $config);

        try {
            $this->database = new Database($config);
        } catch (PDOException $exception) {
            throw new RuntimeException(sprintf(
                'The test suite needs MySQL on %s:%s with the database "%s". '
                . 'Start it with "docker compose up -d mysql" from the app-api directory. Original error: %s',
                (string) $config->get('DB_HOST'),
                (string) $config->get('DB_PORT'),
                (string) $config->get('DB_DATABASE'),
                $exception->getMessage()
            ), 0, $exception);
        }

        $this->kernel->container()->setDatabase($this->database);

        if (!self::$databaseReady) {
            (new Schema($this->database))->fresh();
            (new Seeder($this->database))->run();

            self::$databaseReady = true;
        }

        $this->database->pdo()->beginTransaction();
    }

    protected function tearDown(): void
    {
        // Undo whatever the test wrote, so the next one sees the seeded state
        // again without paying for another round of TRUNCATE and INSERT.
        if ($this->database !== null && $this->database->pdo()->inTransaction()) {
            $this->database->pdo()->rollBack();
        }

        parent::tearDown();
    }

    protected function basePath(): string
    {
        return dirname(__DIR__);
    }

    /**
     * @param array<string, string> $query
     */
    protected function get(string $path, array $query = []): Response
    {
        return $this->kernel->handle(Request::create('GET', $path, $query));
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $headers
     */
    protected function post(string $path, array $payload = [], array $headers = []): Response
    {
        return $this->kernel->handle(Request::create('POST', $path, [], $headers, $payload));
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function postAuthorised(string $path, array $payload = []): Response
    {
        return $this->post($path, $payload, ['X-API-Key' => self::API_KEY]);
    }
}
