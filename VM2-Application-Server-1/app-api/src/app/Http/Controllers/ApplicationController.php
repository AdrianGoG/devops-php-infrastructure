<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Exceptions\HttpException;
use App\Repositories\ApplicationRepository;
use App\Repositories\DeploymentRepository;
use App\Support\StringHelper;

/**
 * The nine PHP applications of the estate.
 *
 * This is the endpoint the Python monitor uses to discover what it has to probe,
 * and the one app-user-dashboard renders.
 *
 * NOTE: the payload is built with App\Support\StringHelper, a file that PHP 8
 * cannot even parse (MIGRATION.md, incompatibility #1). Both endpoints of this
 * controller therefore return HTTP 500 after the upgrade, until the source code
 * is adapted.
 */
class ApplicationController extends Controller
{
    /**
     * @param array<string, string> $parameters
     */
    public function index(Request $request, array $parameters = []): Response
    {
        unset($parameters);

        $filters = [];

        if ($request->query('server') !== null) {
            $filters['server_key'] = (string) $request->query('server');
        }

        if ($request->query('status') !== null) {
            $filters['status'] = (string) $request->query('status');
        }

        if ($request->query('php') !== null) {
            $filters['php_version'] = (string) $request->query('php');
        }

        $repository = new ApplicationRepository($this->database());

        $rows = $repository->all($filters);

        $data = [];

        foreach ($rows as $row) {
            $data[] = $this->present($row);
        }

        return Response::json([
            'data' => $data,
            'meta' => [
                'total' => count($data),
                'filters' => $filters,
                'registered' => $repository->count(),
            ],
        ]);
    }

    /**
     * @param array<string, string> $parameters
     */
    public function show(Request $request, array $parameters = []): Response
    {
        unset($request);

        $name = isset($parameters['name']) ? $parameters['name'] : '';

        $repository = new ApplicationRepository($this->database());

        $row = $repository->findByName($name);

        if ($row === null) {
            throw new HttpException('Application ' . $name . ' is not registered.', 404);
        }

        $deployments = new DeploymentRepository($this->database());

        $payload = $this->present($row);
        $payload['recent_deployments'] = $deployments->recent(['application' => $name], 5);

        return Response::json(['data' => $payload]);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function present(array $row): array
    {
        $name = (string) $row['name'];

        return [
            'name' => $name,
            // LEGACY: curly brace string offsets - parse error on PHP 8.0+.
            'code' => StringHelper::shortCode($name),
            'label' => StringHelper::label($name),
            'server' => (string) $row['server_key'],
            'title' => (string) $row['title'],
            'php_version' => (string) $row['php_version'],
            'php_branch' => StringHelper::majorMinor((string) $row['php_version']),
            'framework' => (string) $row['framework'],
            'port' => (int) $row['port'],
            'status' => (string) $row['status'],
            'note' => (string) $row['note'],
            'url' => $this->buildUrl($row),
        ];
    }

    /**
     * The URL the Python monitor has to probe for this application.
     *
     * @param array<string, mixed> $row
     */
    private function buildUrl(array $row): ?string
    {
        $host = $this->database()->selectOne(
            'SELECT host FROM servers WHERE server_key = :key',
            ['key' => (string) $row['server_key']]
        );

        if ($host === null || $host['host'] === null) {
            return null;
        }

        return 'http://' . $host['host'] . ':' . (int) $row['port'];
    }
}
