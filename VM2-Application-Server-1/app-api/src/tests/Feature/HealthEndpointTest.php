<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\ApiTestCase;

/**
 * The health contract consumed by the Python monitor and by the Jenkins smoke
 * test stage. Any change here breaks the monitoring, so it is asserted field by
 * field.
 */
class HealthEndpointTest extends ApiTestCase
{
    public function test_it_answers_with_200_and_the_expected_contract(): void
    {
        $response = $this->get('/api/health');

        $this->assertSame(200, $response->status());

        $payload = $response->decoded();

        $this->assertSame('ok', $payload['status']);
        $this->assertSame('app-api', $payload['application']);
        $this->assertSame('VM2-Application-Server-1', $payload['server']);
        $this->assertSame('ok', $payload['database']);
        $this->assertSame(PHP_VERSION, $payload['php']);
        $this->assertArrayHasKey('checked_at', $payload);
    }

    public function test_it_reports_how_many_applications_are_registered(): void
    {
        $payload = $this->get('/api/health')->decoded();

        $this->assertSame(9, $payload['applications_registered']);
    }

    /**
     * Guards incompatibility #4 of MIGRATION.md: on PHP 8 the PHP 4 style
     * constructor of LegacyTimer stops being called and this flag flips to
     * false, which is the only visible symptom of that silent breakage.
     */
    public function test_it_reports_that_the_legacy_timer_initialised_itself(): void
    {
        $payload = $this->get('/api/health')->decoded();

        $this->assertTrue(
            $payload['legacy_timer_initialised'],
            'LegacyTimer did not initialise - the PHP 4 style constructor was not called (PHP 8 behaviour).'
        );
    }
}
