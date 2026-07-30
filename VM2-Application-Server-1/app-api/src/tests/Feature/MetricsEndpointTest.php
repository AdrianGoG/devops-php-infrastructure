<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\ApiTestCase;

/**
 * The Prometheus exposition endpoint. Prometheus is strict about the format, so
 * the HELP/TYPE lines and the metric names are asserted explicitly.
 */
class MetricsEndpointTest extends ApiTestCase
{
    public function test_it_is_served_as_prometheus_text(): void
    {
        $response = $this->get('/metrics');

        $this->assertSame(200, $response->status());
        $this->assertStringContainsString('text/plain', $response->headers()['Content-Type']);
    }

    public function test_it_exposes_the_inventory_counters(): void
    {
        $body = $this->get('/metrics')->body();

        $this->assertStringContainsString('# TYPE api_up gauge', $body);
        $this->assertStringContainsString("\napi_up 1", $body);
        $this->assertStringContainsString("\napi_servers_total 4", $body);
        $this->assertStringContainsString("\napi_applications_total 9", $body);
        $this->assertStringContainsString('api_php_version_info{version="' . PHP_VERSION . '"} 1', $body);
    }

    public function test_it_exposes_the_deployment_counters_per_result(): void
    {
        $body = $this->get('/metrics')->body();

        $this->assertStringContainsString('api_deployments_total 8', $body);
        $this->assertStringContainsString('api_deployments_by_result{result="success"} 5', $body);
        $this->assertStringContainsString('api_deployments_by_result{result="failed"} 2', $body);
        $this->assertStringContainsString('api_deployments_by_result{result="rolled_back"} 1', $body);
    }

    public function test_it_exposes_the_application_status_breakdown(): void
    {
        $body = $this->get('/metrics')->body();

        $this->assertStringContainsString('api_applications_by_status{status="legacy"} 3', $body);
        $this->assertStringContainsString('api_applications_by_status{status="blocked"} 3', $body);
        $this->assertStringContainsString('api_applications_by_status{status="pending"} 2', $body);
        $this->assertStringContainsString('api_applications_by_status{status="ok"} 1', $body);
    }

    /**
     * Guards incompatibility #4 of MIGRATION.md from the metrics side: after the
     * PHP 8 upgrade this gauge drops to 0.
     */
    public function test_the_legacy_timer_gauge_reports_one(): void
    {
        $this->assertStringContainsString('api_legacy_timer_initialised 1', $this->get('/metrics')->body());
    }
}
