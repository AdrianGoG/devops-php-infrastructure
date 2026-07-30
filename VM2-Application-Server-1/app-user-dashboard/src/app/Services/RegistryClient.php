<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Reads the infrastructure registry exposed by app-api (VM2, port 8083).
 *
 * The dashboard owns no infrastructure data of its own: it only knows how to
 * ask. This is also the demonstration that two applications on the same server,
 * running different PHP versions (8.2 here, 7.4 there), talk over HTTP.
 *
 * Every call fails soft. If the API is down - which it will be during a PHP
 * upgrade - the dashboard shows a warning instead of a 500 page.
 */
class RegistryClient
{
    /**
     * Fetch the servers of the infrastructure.
     *
     * @return array<int, array<string, mixed>>
     */
    public function servers(): array
    {
        return $this->get('/api/servers');
    }

    /**
     * Fetch the hosted applications.
     *
     * @return array<int, array<string, mixed>>
     */
    public function applications(): array
    {
        return $this->get('/api/applications');
    }

    /**
     * Fetch the most recent deployments recorded by the pipeline.
     *
     * @return array<int, array<string, mixed>>
     */
    public function deployments(int $limit = 8): array
    {
        return $this->get('/api/deployments', ['limit' => $limit]);
    }

    /**
     * Whether the registry answered the last time it was asked.
     */
    public function isReachable(): bool
    {
        return $this->lastCallSucceeded;
    }

    public function baseUrl(): string
    {
        return (string) config('registry.base_url');
    }

    /** @var bool */
    private $lastCallSucceeded = true;

    /**
     * Perform a GET request and return the "data" element of the payload.
     *
     * Responses are cached for a few seconds so that a single page render does
     * not hit the API three times, and a page refresh does not either.
     *
     * @param array<string, mixed> $query
     * @return array<int, array<string, mixed>>
     */
    private function get(string $path, array $query = []): array
    {
        $cacheKey = 'registry:' . $path . ':' . http_build_query($query);

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        try {
            $response = Http::baseUrl($this->baseUrl())
                ->timeout((int) config('registry.timeout'))
                ->acceptJson()
                ->get($path, $query);

            if ($response->failed()) {
                $this->lastCallSucceeded = false;

                Log::warning('The infrastructure registry answered with an error.', [
                    'path' => $path,
                    'status' => $response->status(),
                ]);

                return [];
            }

            $data = $response->json('data');

            if (! is_array($data)) {
                $this->lastCallSucceeded = false;

                return [];
            }

            Cache::put($cacheKey, $data, (int) config('registry.cache_seconds'));

            return $data;
        } catch (Throwable $exception) {
            // Connection refused, DNS failure, timeout - the API is simply down.
            $this->lastCallSucceeded = false;

            Log::warning('The infrastructure registry is unreachable.', [
                'path' => $path,
                'error' => $exception->getMessage(),
            ]);

            return [];
        }
    }
}
