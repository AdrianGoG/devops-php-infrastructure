<?php

/**
 * app-api - front controller.
 *
 * Every HTTP request of the API enters here. Nginx rewrites all URIs to this
 * file (see docker/nginx/default.conf).
 *
 * PHP 7.4. The application intentionally uses constructs that PHP 8 removed;
 * MIGRATION.md documents each one of them.
 */

declare(strict_types=1);

use App\Core\Kernel;
use App\Core\Request;

require __DIR__ . '/../vendor/autoload.php';

$kernel = new Kernel(dirname(__DIR__));

$kernel->handle(Request::fromGlobals())->send();
