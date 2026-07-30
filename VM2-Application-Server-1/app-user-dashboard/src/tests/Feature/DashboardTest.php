<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The dashboard page and its dependency on app-api.
 *
 * The registry is always faked: the tests must pass in the Jenkins pipeline
 * whether or not app-api happens to be running.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function fakeRegistry(): void
    {
        Http::fake([
            '*/api/servers' => Http::response(['data' => [
                [
                    'key' => 'vm2',
                    'name' => 'VM2 - Application Server 1',
                    'host' => '192.168.0.170',
                    'is_controller' => false,
                    'applications_count' => 3,
                ],
            ]]),
            '*/api/applications' => Http::response(['data' => [
                [
                    'name' => 'app-api',
                    'title' => 'Infrastructure registry API',
                    'server' => 'vm2',
                    'php_version' => '7.4',
                    'port' => 8083,
                    'status' => 'legacy',
                    'url' => 'http://192.168.0.170:8083',
                ],
                [
                    'name' => 'app-company-website',
                    'title' => 'Presentation site',
                    'server' => 'vm2',
                    'php_version' => '8.3',
                    'port' => 8081,
                    'status' => 'ok',
                    'url' => 'http://192.168.0.170:8081',
                ],
            ]]),
            '*/api/deployments*' => Http::response(['data' => [
                [
                    'application' => 'app-company-website',
                    'build_number' => 128,
                    'branch' => 'main',
                    'result' => 'success',
                    'deployed_at' => '2026-07-30 08:41:19',
                ],
                [
                    'application' => 'app-crm',
                    'build_number' => 18,
                    'branch' => 'development',
                    'result' => 'rolled_back',
                    'deployed_at' => '2026-07-26 16:22:05',
                ],
            ]]),
        ]);
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_the_root_url_sends_guests_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_the_root_url_sends_authenticated_users_to_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertRedirect('/dashboard');
    }

    public function test_an_authenticated_user_sees_the_registry_data(): void
    {
        $this->fakeRegistry();

        $response = $this->actingAs(User::factory()->create())->get('/dashboard');

        $response->assertOk();
        $response->assertSee('app-api');
        $response->assertSee('app-company-website');
        $response->assertSee('registry online', false);
    }

    public function test_it_counts_servers_applications_legacy_apps_and_failed_deployments(): void
    {
        $this->fakeRegistry();

        $response = $this->actingAs(User::factory()->create())->get('/dashboard');

        $response->assertOk();

        $stats = $response->viewData('stats');

        $this->assertSame(1, $stats['servers']);
        $this->assertSame(2, $stats['applications']);
        $this->assertSame(1, $stats['legacy']);
        $this->assertSame(1, $stats['failed_deployments']);
    }

    public function test_the_dashboard_still_renders_when_the_registry_is_down(): void
    {
        // Connection refused, exactly like app-api being rebuilt on PHP 8.3.
        Http::fake(['*' => Http::failedConnection()]);

        $response = $this->actingAs(User::factory()->create())->get('/dashboard');

        $response->assertOk();
        $response->assertSee('registry offline', false);
        $response->assertSee('The registry API is not answering.', false);
    }

    public function test_the_dashboard_reports_a_registry_error_response(): void
    {
        Http::fake(['*' => Http::response('', 503)]);

        $response = $this->actingAs(User::factory()->create())->get('/dashboard');

        $response->assertOk();
        $response->assertSee('registry offline', false);
        $this->assertSame(0, $response->viewData('stats')['applications']);
    }

    public function test_the_health_endpoint_answers_without_authentication(): void
    {
        $response = $this->getJson('/health');

        $response->assertOk();
        $response->assertJson([
            'status' => 'ok',
            'application' => 'app-user-dashboard',
            'server' => 'VM2-Application-Server-1',
        ]);
        $this->assertSame(PHP_VERSION, $response->json('php'));
    }
}
