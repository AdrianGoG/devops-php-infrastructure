<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Exceptions\HttpException;
use App\Http\Middleware\ApiKeyGuard;
use App\Repositories\ApplicationRepository;
use App\Repositories\DeploymentRepository;
use App\Support\Collection;
use App\Support\Validator;

/**
 * The deployment log of the infrastructure.
 *
 * The Jenkins pipeline finishes by POSTing here, which turns the log into the
 * single place where the whole estate's deployment history lives - readable by
 * app-user-dashboard and by the monitoring.
 *
 * NOTE: index() aggregates through App\Support\Collection, which uses each()
 * (MIGRATION.md, incompatibility #3), so listing breaks on PHP 8 while writing
 * keeps working.
 */
class DeploymentController extends Controller
{
    const RESULTS = ['success', 'failed', 'rolled_back'];

    /**
     * @param array<string, string> $parameters
     */
    public function index(Request $request, array $parameters = []): Response
    {
        unset($parameters);

        $filters = [];

        if ($request->query('application') !== null) {
            $filters['application'] = (string) $request->query('application');
        }

        if ($request->query('result') !== null) {
            $filters['result'] = (string) $request->query('result');
        }

        $limit = (int) $request->query('limit', '25');

        $repository = new DeploymentRepository($this->database());

        $rows = $repository->recent($filters, $limit > 0 ? $limit : 25);

        return Response::json([
            'data' => $rows,
            'meta' => [
                'total' => count($rows),
                'filters' => $filters,
                // LEGACY: each() - removed in PHP 8.0.
                'results' => Collection::summarise($rows, 'result'),
                'average_duration_seconds' => Collection::average($rows, 'duration_seconds'),
                'recorded_overall' => $repository->count(),
            ],
        ]);
    }

    /**
     * Record a deployment. Called by the Jenkins pipeline.
     *
     * @param array<string, string> $parameters
     */
    public function store(Request $request, array $parameters = []): Response
    {
        unset($parameters);

        ApiKeyGuard::check($request, $this->config());

        $payload = $request->json();

        $validator = new Validator();

        $valid = $validator->validate($payload, [
            'application' => ['required', 'max:120'],
            'result' => ['required', 'in:' . implode(',', self::RESULTS)],
            'branch' => ['max:120'],
            'commit_sha' => ['max:64'],
            'build_number' => ['integer'],
            'duration_seconds' => ['integer'],
            'notes' => ['max:1000'],
        ]);

        if (!$valid) {
            throw new HttpException('The payload is not valid.', 422, ['fields' => $validator->errors()]);
        }

        $applications = new ApplicationRepository($this->database());

        $application = (string) $payload['application'];

        if ($applications->findByName($application) === null) {
            throw new HttpException('Application ' . $application . ' is not registered.', 422);
        }

        $repository = new DeploymentRepository($this->database());

        $id = $repository->create([
            'application' => $application,
            'build_number' => isset($payload['build_number']) ? (int) $payload['build_number'] : null,
            'branch' => isset($payload['branch']) ? (string) $payload['branch'] : null,
            'commit_sha' => isset($payload['commit_sha']) ? (string) $payload['commit_sha'] : null,
            'result' => (string) $payload['result'],
            'duration_seconds' => isset($payload['duration_seconds']) ? (int) $payload['duration_seconds'] : null,
            'deployed_at' => date('Y-m-d H:i:s'),
            'notes' => isset($payload['notes']) ? (string) $payload['notes'] : null,
        ]);

        $this->logger()->info('deployment recorded', [
            'id' => $id,
            'application' => $application,
            'result' => (string) $payload['result'],
        ]);

        return Response::json(['data' => $repository->find($id)], 201);
    }
}
