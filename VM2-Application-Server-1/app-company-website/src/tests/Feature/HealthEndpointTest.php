<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The /health endpoint is the contract between the application and the
 * infrastructure: the Python monitoring utility and the Jenkins smoke test
 * stage both depend on the exact shape of this response.
 */
class HealthEndpointTest extends TestCase
{
    public function test_the_health_endpoint_responds_with_200_and_valid_json(): void
    {
        $response = $this->getJson('/health');

        $response->assertOk();
        $response->assertJson([
            'status' => 'ok',
            'application' => 'app-company-website',
            'server' => 'VM2-Application-Server-1',
        ]);
    }

    public function test_the_health_endpoint_reports_the_php_version(): void
    {
        $response = $this->getJson('/health');

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'application',
            'server',
            'php',
            'laravel',
            'environment',
            'checked_at',
        ]);

        $this->assertSame(PHP_VERSION, $response->json('php'));
    }
}
