<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Config;
use App\Core\Container;
use App\Core\Database;
use App\Core\Logger;

/**
 * Base controller: gives every endpoint access to the shared services.
 */
abstract class Controller
{
    /** @var Container */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    protected function config(): Config
    {
        return $this->container->config();
    }

    protected function database(): Database
    {
        return $this->container->database();
    }

    protected function logger(): Logger
    {
        return $this->container->logger();
    }
}
