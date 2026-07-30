<?php

declare(strict_types=1);

use App\Core\Router;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\DeploymentController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\MetricsController;
use App\Http\Controllers\ServerController;

/*
|--------------------------------------------------------------------------
| API routes
|--------------------------------------------------------------------------
|
| app-api · VM2 Application Server 1 · PHP 7.4 · port 8083
|
| Consumers of these endpoints:
|   - python-monitor  -> GET /api/health, GET /api/applications
|   - Jenkins         -> POST /api/deployments at the end of the pipeline
|   - Prometheus      -> GET /metrics
|   - app-user-dashboard -> everything else
|
*/

return function (Router $router) {
    $router->get('/', [IndexController::class, 'index']);
    $router->get('/api/health', [HealthController::class, 'show']);

    $router->get('/api/servers', [ServerController::class, 'index']);
    $router->get('/api/servers/{key}', [ServerController::class, 'show']);

    $router->get('/api/applications', [ApplicationController::class, 'index']);
    $router->get('/api/applications/{name}', [ApplicationController::class, 'show']);

    $router->get('/api/deployments', [DeploymentController::class, 'index']);
    $router->post('/api/deployments', [DeploymentController::class, 'store']);

    $router->get('/metrics', [MetricsController::class, 'show']);
};
