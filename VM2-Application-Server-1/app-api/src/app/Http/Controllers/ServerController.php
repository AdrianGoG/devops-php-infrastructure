<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Exceptions\HttpException;
use App\Repositories\ApplicationRepository;
use App\Repositories\ServerRepository;
use App\Support\Sorter;

/**
 * The servers of the infrastructure.
 *
 * NOTE: index() sorts through App\Support\Sorter, which relies on
 * create_function(). This endpoint is therefore one of the casualties of the
 * PHP 8 upgrade (MIGRATION.md, incompatibility #2), while show() keeps working -
 * exactly the kind of partial outage the monitoring report is meant to reveal.
 */
class ServerController extends Controller
{
    /**
     * @param array<string, string> $parameters
     */
    public function index(Request $request, array $parameters = []): Response
    {
        unset($parameters);

        $servers = new ServerRepository($this->database());
        $applications = new ApplicationRepository($this->database());

        $rows = $servers->all();

        // LEGACY: create_function() - removed in PHP 8.0.
        $rows = Sorter::byField($rows, 'server_key');

        $byServer = Sorter::groupBy($applications->all(), 'server_key');

        $data = [];

        foreach ($rows as $row) {
            $key = (string) $row['server_key'];

            $data[] = $this->present($row, isset($byServer[$key]) ? $byServer[$key] : []);
        }

        return Response::json([
            'data' => $data,
            'meta' => [
                'total' => count($data),
                'include_applications' => $request->query('applications', '1') !== '0',
            ],
        ]);
    }

    /**
     * @param array<string, string> $parameters
     */
    public function show(Request $request, array $parameters = []): Response
    {
        unset($request);

        $key = isset($parameters['key']) ? $parameters['key'] : '';

        $servers = new ServerRepository($this->database());

        $row = $servers->findByKey($key);

        if ($row === null) {
            throw new HttpException('Server ' . $key . ' is not part of the inventory.', 404);
        }

        $applications = new ApplicationRepository($this->database());

        return Response::json([
            'data' => $this->present($row, $applications->all(['server_key' => $key])),
        ]);
    }

    /**
     * @param array<string, mixed> $row
     * @param list<array<string, mixed>> $applications
     * @return array<string, mixed>
     */
    private function present(array $row, array $applications): array
    {
        $names = [];

        foreach ($applications as $application) {
            $names[] = (string) $application['name'];
        }

        return [
            'key' => (string) $row['server_key'],
            'name' => (string) $row['name'],
            'role' => (string) $row['role'],
            'host' => $row['host'] !== null ? (string) $row['host'] : null,
            'os' => (string) $row['os'],
            'is_controller' => (bool) $row['is_controller'],
            'summary' => (string) $row['summary'],
            'applications' => $names,
            'applications_count' => count($names),
        ];
    }
}
