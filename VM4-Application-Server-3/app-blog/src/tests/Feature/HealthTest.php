<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The health contract consumed by the Python monitor, app-monitor and the
 * Jenkins smoke test.
 */
class HealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_answers_with_200_and_the_expected_contract(): void
    {
        Post::factory()->count(3)->create();
        Post::factory()->draft()->count(2)->create();

        $response = $this->getJson('/health');

        $response->assertOk();
        $response->assertJson([
            'status' => 'ok',
            'application' => 'app-blog',
            'server' => 'VM4-Application-Server-3',
            'database' => 'ok',
            'posts' => 5,
            'published' => 3,
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