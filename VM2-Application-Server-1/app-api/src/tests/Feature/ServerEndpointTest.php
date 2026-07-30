<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\ApiTestCase;

class ServerEndpointTest extends ApiTestCase
{
    public function test_it_lists_the_four_servers_of_the_infrastructure(): void
    {
        $response = $this->get('/api/servers');

        $this->assertSame(200, $response->status());

        $payload = $response->decoded();

        $this->assertCount(4, $payload['data']);
        $this->assertSame(4, $payload['meta']['total']);

        $keys = array_column($payload['data'], 'key');

        $this->assertSame(['vm1', 'vm2', 'vm3', 'vm4'], $keys);
    }

    public function test_the_control_node_is_flagged_and_has_no_host(): void
    {
        $payload = $this->get('/api/servers')->decoded();

        $controller = $payload['data'][0];

        $this->assertSame('vm1', $controller['key']);
        $this->assertTrue($controller['is_controller']);
        $this->assertNull($controller['host']);
        $this->assertSame(0, $controller['applications_count']);
    }

    public function test_each_application_server_reports_three_applications(): void
    {
        $payload = $this->get('/api/servers')->decoded();

        foreach ($payload['data'] as $server) {
            if ($server['is_controller']) {
                continue;
            }

            $this->assertSame(3, $server['applications_count'], $server['key'] . ' should host three applications.');
        }
    }

    public function test_it_returns_a_single_server_with_its_applications(): void
    {
        $response = $this->get('/api/servers/vm2');

        $this->assertSame(200, $response->status());

        $data = $response->decoded()['data'];

        $this->assertSame('192.168.0.170', $data['host']);
        $this->assertContains('app-api', $data['applications']);
    }

    public function test_an_unknown_server_returns_404(): void
    {
        $response = $this->get('/api/servers/vm9');

        $this->assertSame(404, $response->status());
        $this->assertSame(404, $response->decoded()['error']['status']);
    }
}
