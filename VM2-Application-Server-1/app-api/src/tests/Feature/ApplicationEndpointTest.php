<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\ApiTestCase;

class ApplicationEndpointTest extends ApiTestCase
{
    public function test_it_lists_the_nine_applications_of_the_estate(): void
    {
        $response = $this->get('/api/applications');

        $this->assertSame(200, $response->status());

        $payload = $response->decoded();

        $this->assertCount(9, $payload['data']);
        $this->assertSame(9, $payload['meta']['registered']);
    }

    public function test_it_filters_by_server(): void
    {
        $payload = $this->get('/api/applications', ['server' => 'vm3'])->decoded();

        $this->assertCount(3, $payload['data']);

        foreach ($payload['data'] as $application) {
            $this->assertSame('vm3', $application['server']);
        }
    }

    public function test_it_filters_by_status_and_by_php_version(): void
    {
        $legacy = $this->get('/api/applications', ['status' => 'legacy'])->decoded();

        $this->assertNotEmpty($legacy['data']);

        foreach ($legacy['data'] as $application) {
            $this->assertSame('legacy', $application['status']);
        }

        // app-api and app-crm are the two applications still on PHP 7.4.
        $seven = $this->get('/api/applications', ['php' => '7.4'])->decoded();

        $this->assertCount(2, $seven['data']);

        // The three VM4 applications are blocked the other way round: their
        // runtime is below what Laravel 13 requires.
        $blocked = $this->get('/api/applications', ['status' => 'blocked'])->decoded();

        $this->assertCount(3, $blocked['data']);

        foreach ($blocked['data'] as $application) {
            $this->assertSame('vm4', $application['server']);
            $this->assertSame('8.2', $application['php_version']);
        }
    }

    /**
     * Guards incompatibility #1 of MIGRATION.md: the short code is produced by
     * StringHelper, a file PHP 8 cannot parse.
     */
    public function test_it_exposes_the_short_code_built_by_the_legacy_string_helper(): void
    {
        $payload = $this->get('/api/applications/app-company-website')->decoded();

        $this->assertSame('ACW', $payload['data']['code']);
        $this->assertSame('App Company Website', $payload['data']['label']);
    }

    public function test_it_returns_a_single_application_with_its_probe_url(): void
    {
        $response = $this->get('/api/applications/app-api');

        $this->assertSame(200, $response->status());

        $data = $response->decoded()['data'];

        $this->assertSame('7.4', $data['php_version']);
        $this->assertSame(8083, $data['port']);
        $this->assertSame('legacy', $data['status']);
        $this->assertSame('http://192.168.0.169:8083', $data['url']);
    }

    public function test_a_single_application_includes_its_recent_deployments(): void
    {
        $data = $this->get('/api/applications/app-company-website')->decoded()['data'];

        $this->assertNotEmpty($data['recent_deployments']);
        $this->assertSame('app-company-website', $data['recent_deployments'][0]['application']);
    }

    public function test_an_unknown_application_returns_404(): void
    {
        $this->assertSame(404, $this->get('/api/applications/app-does-not-exist')->status());
    }
}
