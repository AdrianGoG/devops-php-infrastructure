<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\ApiTestCase;

/**
 * The deployment log: what Jenkins writes at the end of the pipeline and what
 * app-user-dashboard reads.
 */
class DeploymentEndpointTest extends ApiTestCase
{
    public function test_it_lists_the_seeded_deployments_newest_first(): void
    {
        $response = $this->get('/api/deployments');

        $this->assertSame(200, $response->status());

        $payload = $response->decoded();

        $this->assertSame(8, $payload['meta']['recorded_overall']);
        $this->assertSame('app-api', $payload['data'][0]['application']);
    }

    /**
     * Guards incompatibility #3 of MIGRATION.md: the summary is produced with
     * each(), removed in PHP 8.
     */
    public function test_it_summarises_the_results_with_the_legacy_collection_helper(): void
    {
        $meta = $this->get('/api/deployments')->decoded()['meta'];

        $this->assertSame(5, $meta['results']['success']);
        $this->assertSame(2, $meta['results']['failed']);
        $this->assertSame(1, $meta['results']['rolled_back']);
        $this->assertGreaterThan(0, $meta['average_duration_seconds']);
    }

    public function test_it_filters_by_application_and_respects_the_limit(): void
    {
        $payload = $this->get('/api/deployments', ['application' => 'app-company-website'])->decoded();

        $this->assertCount(3, $payload['data']);

        $limited = $this->get('/api/deployments', ['limit' => '2'])->decoded();

        $this->assertCount(2, $limited['data']);
    }

    public function test_jenkins_can_record_a_deployment(): void
    {
        $response = $this->postAuthorised('/api/deployments', [
            'application' => 'app-api',
            'build_number' => 42,
            'branch' => 'development',
            'commit_sha' => 'aa11bb22cc33dd44ee55ff66aa77bb88cc99dd00',
            'result' => 'success',
            'duration_seconds' => 71,
            'notes' => 'Recorded by the pipeline.',
        ]);

        $this->assertSame(201, $response->status());

        $data = $response->decoded()['data'];

        $this->assertSame('app-api', $data['application']);
        $this->assertSame('success', $data['result']);
        $this->assertSame(42, $data['build_number']);
        $this->assertSame(71, $data['duration_seconds']);

        $this->assertSame(9, $this->get('/api/deployments')->decoded()['meta']['recorded_overall']);
    }

    public function test_writing_without_the_api_key_is_rejected(): void
    {
        $response = $this->post('/api/deployments', [
            'application' => 'app-api',
            'result' => 'success',
        ]);

        $this->assertSame(401, $response->status());
        $this->assertSame(8, $this->get('/api/deployments')->decoded()['meta']['recorded_overall']);
    }

    public function test_a_wrong_api_key_is_rejected(): void
    {
        $response = $this->post('/api/deployments', [
            'application' => 'app-api',
            'result' => 'success',
        ], ['X-API-Key' => 'not-the-key']);

        $this->assertSame(401, $response->status());
    }

    public function test_the_payload_is_validated(): void
    {
        $response = $this->postAuthorised('/api/deployments', ['result' => 'exploded']);

        $this->assertSame(422, $response->status());

        $fields = $response->decoded()['error']['fields'];

        $this->assertArrayHasKey('application', $fields);
        $this->assertArrayHasKey('result', $fields);
    }

    public function test_a_deployment_of_an_unregistered_application_is_rejected(): void
    {
        $response = $this->postAuthorised('/api/deployments', [
            'application' => 'app-ghost',
            'result' => 'success',
        ]);

        $this->assertSame(422, $response->status());
    }
}
