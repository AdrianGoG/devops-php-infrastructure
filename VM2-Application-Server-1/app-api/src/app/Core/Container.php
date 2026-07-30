<?php

declare(strict_types=1);

namespace App\Core;

/**
 * The services shared by the whole application.
 *
 * Explicit and tiny on purpose: controllers receive it in their constructor,
 * which keeps them trivial to build inside a test without any framework.
 */
class Container
{
    /** @var string */
    private $basePath;

    /** @var Config */
    private $config;

    /** @var Logger */
    private $logger;

    /** @var Database|null */
    private $database;

    public function __construct(string $basePath, Config $config, Logger $logger)
    {
        $this->basePath = $basePath;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function logger(): Logger
    {
        return $this->logger;
    }

    /**
     * The connection is opened on first use, so endpoints that do not touch
     * the database (such as the API index) still answer when MySQL is down.
     */
    public function database(): Database
    {
        if ($this->database === null) {
            $this->database = new Database($this->config);
        }

        return $this->database;
    }

    /**
     * Used by the test suite and by the console to inject a prepared connection.
     */
    public function setDatabase(Database $database): void
    {
        $this->database = $database;
    }
}
