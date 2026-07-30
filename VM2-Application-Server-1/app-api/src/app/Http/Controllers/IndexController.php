<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Request;
use App\Core\Response;

/**
 * The API index - a self describing entry point.
 *
 * Deliberately free of any database or legacy dependency, so it keeps answering
 * even when the rest of the service is broken. That makes it the first thing to
 * check when the Python monitor reports a problem.
 */
class IndexController extends Controller
{
    const ENDPOINTS = [
        'GET /api/health' => 'Health check consumed by the Python monitor and the Jenkins smoke test',
        'GET /api/servers' => 'The servers of the infrastructure',
        'GET /api/servers/{key}' => 'One server, by its inventory key (vm1 … vm4)',
        'GET /api/applications' => 'The hosted applications; filters: server, status, php',
        'GET /api/applications/{name}' => 'One application, by name',
        'GET /api/deployments' => 'The deployment log; filters: application, result, limit',
        'POST /api/deployments' => 'Record a deployment (requires the X-API-Key header)',
        'GET /metrics' => 'Prometheus exposition format',
    ];

    /**
     * @param array<string, string> $parameters
     */
    public function index(Request $request, array $parameters = []): Response
    {
        unset($request, $parameters);

        return Response::json([
            'data' => [
                'application' => 'app-api',
                'description' => (string) $this->config()->get('APP_NAME', 'Infrastructure Registry API'),
                'server' => 'VM2-Application-Server-1',
                'php' => PHP_VERSION,
                'environment' => (string) $this->config()->get('APP_ENV', 'production'),
                'endpoints' => self::ENDPOINTS,
            ],
        ]);
    }
}
