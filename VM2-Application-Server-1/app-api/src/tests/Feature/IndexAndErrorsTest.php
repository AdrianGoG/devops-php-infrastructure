<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\ApiTestCase;

class IndexAndErrorsTest extends ApiTestCase
{
    public function test_the_index_describes_the_api(): void
    {
        $response = $this->get('/');

        $this->assertSame(200, $response->status());

        $data = $response->decoded()['data'];

        $this->assertSame('app-api', $data['application']);
        $this->assertSame(PHP_VERSION, $data['php']);
        $this->assertArrayHasKey('GET /api/health', $data['endpoints']);
    }

    public function test_an_unknown_endpoint_returns_a_json_404(): void
    {
        $response = $this->get('/api/nope');

        $this->assertSame(404, $response->status());
        $this->assertStringContainsString('application/json', $response->headers()['Content-Type']);
        $this->assertStringContainsString('does not exist', $response->decoded()['error']['message']);
    }

    public function test_a_wrong_method_returns_405(): void
    {
        $response = $this->post('/api/servers', []);

        $this->assertSame(405, $response->status());
        $this->assertStringContainsString('not supported', $response->decoded()['error']['message']);
    }

    public function test_trailing_slashes_resolve_to_the_same_endpoint(): void
    {
        $this->assertSame(200, $this->get('/api/servers/')->status());
    }
}
