<?php

namespace Tests\Feature;

use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The health contract consumed by the Python monitor and the Jenkins smoke test.
 */
class HealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_answers_with_200_and_the_expected_contract(): void
    {
        Ticket::factory()->open()->count(2)->create();
        Ticket::factory()->closed()->count(3)->create();

        $response = $this->getJson('/health');

        $response->assertOk();
        $response->assertJson([
            'status' => 'ok',
            'application' => 'app-ticket-system',
            'server' => 'VM3-Application-Server-2',
            'database' => 'ok',
            'tickets' => 5,
            'unresolved' => 2,
        ]);
    }

    public function test_it_reports_the_php_and_laravel_versions(): void
    {
        $response = $this->getJson('/health');

        $response->assertOk();
        $this->assertSame(PHP_VERSION, $response->json('php'));
        $this->assertSame(app()->version(), $response->json('laravel'));
    }
}
