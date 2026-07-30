<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Probes the health endpoint of every application in the estate.
 *
 * This is the PHP counterpart of the Python monitoring utility: the script runs
 * from the control node around a deployment, this runs continuously and feeds
 * Prometheus. Both read the same health contract.
 */
class EstateProbe
{
    /**
     * Probe every configured application.
     *
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        return Cache::remember('estate:probe', (int) config('estate.cache_seconds'), function (): array {
            $results = [];

            foreach ((array) config('estate.applications') as $application) {
                $results[] = $this->probe($application);
            }

            return $results;
        });
    }

    /**
     * Probe one application and normalise the outcome.
     *
     * @param array<string, string> $application
     * @return array<string, mixed>
     */
    public function probe(array $application): array
    {
        $startedAt = microtime(true);

        $result = [
            'name' => $application['name'],
            'server' => $application['server'],
            'url' => $application['url'],
            'reachable' => false,
            'http_status' => null,
            'status' => 'unreachable',
            'php' => null,
            'response_ms' => null,
            'error' => null,
        ];

        try {
            $response = Http::timeout((int) config('estate.timeout'))
                ->acceptJson()
                ->get($application['url']);

            $result['reachable'] = true;
            $result['http_status'] = $response->status();
            $result['response_ms'] = (int) round((microtime(true) - $startedAt) * 1000);

            $payload = $response->json();

            if (is_array($payload)) {
                $result['php'] = $payload['php'] ?? null;
                $result['status'] = (string) ($payload['status'] ?? ($response->successful() ? 'ok' : 'degraded'));
            } else {
                // A 500 from a broken application has no JSON body at all - which
                // is exactly what a PHP version mismatch produces.
                $result['status'] = $response->successful() ? 'ok' : 'down';
            }

            if (! $response->successful() && $result['status'] === 'ok') {
                $result['status'] = 'down';
            }
        } catch (Throwable $exception) {
            $result['error'] = $exception->getMessage();
            $result['response_ms'] = (int) round((microtime(true) - $startedAt) * 1000);
        }

        return $result;
    }

    /**
     * Counters for the dashboard header and the metrics endpoint.
     *
     * @param list<array<string, mixed>> $results
     * @return array<string, int>
     */
    public function summarise(array $results): array
    {
        $healthy = 0;
        $degraded = 0;
        $down = 0;

        foreach ($results as $result) {
            if ($result['status'] === 'ok') {
                $healthy++;
            } elseif ($result['status'] === 'degraded') {
                $degraded++;
            } else {
                $down++;
            }
        }

        return [
            'total' => count($results),
            'healthy' => $healthy,
            'degraded' => $degraded,
            'down' => $down,
        ];
    }
}