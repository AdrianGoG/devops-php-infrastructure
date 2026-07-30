<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The dashboard and the Prometheus endpoint.
 *
 * The estate is always faked: the suite must pass in the pipeline whether or
 * not the other eight applications happen to be running.
 */
class MonitorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The probe caches its results; each test starts from a clean slate.
        Cache::flush();

        config()->set('estate.applications', [
            ['name' => 'app-healthy', 'server' => 'vm2', 'url' => 'http://healthy.test/health'],
            ['name' => 'app-broken', 'server' => 'vm3', 'url' => 'http://broken.test/health'],
            ['name' => 'app-offline', 'server' => 'vm4', 'url' => 'http://offline.test/health'],
        ]);
    }

    private function fakeEstate(): void
    {
        Http::fake([
            'healthy.test/*' => Http::response(['status' => 'ok', 'php' => '8.3.10'], 200),
            // A PHP version mismatch produces a 500 with no JSON body at all.
            'broken.test/*' => Http::response('', 500),
            'offline.test/*' => Http::failedConnection(),
        ]);
    }

    public function test_the_dashboard_lists_every_probed_application(): void
    {
        $this->fakeEstate();

        $response = $this->get(route('monitor.index'));

        $response->assertOk();
        $response->assertSee('app-healthy');
        $response->assertSee('app-broken');
        $response->assertSee('app-offline');
    }

    public function test_it_counts_healthy_and_down_applications(): void
    {
        $this->fakeEstate();

        $response = $this->get(route('monitor.index'));

        $summary = $response->viewData('summary');

        $this->assertSame(3, $summary['total']);
        $this->assertSame(1, $summary['healthy']);
        $this->assertSame(2, $summary['down']);
    }

    public function test_an_application_answering_500_without_a_body_is_reported_as_down(): void
    {
        $this->fakeEstate();

        $results = collect($this->get(route('monitor.index'))->viewData('results'))
            ->keyBy('name');

        $this->assertSame('down', $results['app-broken']['status']);
        $this->assertSame(500, $results['app-broken']['http_status']);
        $this->assertTrue($results['app-broken']['reachable']);
    }

    public function test_an_unreachable_application_is_reported_without_crashing(): void
    {
        $this->fakeEstate();

        $results = collect($this->get(route('monitor.index'))->viewData('results'))
            ->keyBy('name');

        $this->assertSame('unreachable', $results['app-offline']['status']);
        $this->assertFalse($results['app-offline']['reachable']);
        $this->assertNotNull($results['app-offline']['error']);
    }

    public function test_it_records_the_php_version_each_application_reports(): void
    {
        $this->fakeEstate();

        $results = collect($this->get(route('monitor.index'))->viewData('results'))
            ->keyBy('name');

        $this->assertSame('8.3.10', $results['app-healthy']['php']);
        $this->assertNull($results['app-broken']['php']);
    }

    public function test_the_metrics_endpoint_is_served_as_prometheus_text(): void
    {
        $this->fakeEstate();

        $response = $this->get(route('monitor.metrics'));

        $response->assertOk();
        $this->assertStringContainsString('text/plain', $response->headers->get('Content-Type'));
    }

    public function test_the_metrics_expose_one_gauge_per_application(): void
    {
        $this->fakeEstate();

        $body = $this->get(route('monitor.metrics'))->getContent();

        $this->assertStringContainsString('estate_applications_total 3', $body);
        $this->assertStringContainsString('estate_applications_healthy 1', $body);
        $this->assertStringContainsString('estate_applications_down 2', $body);
        $this->assertStringContainsString('estate_application_up{application="app-healthy",server="vm2"} 1', $body);
        $this->assertStringContainsString('estate_application_up{application="app-broken",server="vm3"} 0', $body);
        $this->assertStringContainsString('estate_application_up{application="app-offline",server="vm4"} 0', $body);
    }

    public function test_the_metrics_expose_the_php_version_of_each_application(): void
    {
        $this->fakeEstate();

        $body = $this->get(route('monitor.metrics'))->getContent();

        $this->assertStringContainsString(
            'estate_application_php_version_info{application="app-healthy",version="8.3.10"} 1',
            $body
        );
    }

    public function test_its_own_health_endpoint_answers(): void
    {
        $response = $this->getJson('/health');

        $response->assertOk();
        $response->assertJson([
            'status' => 'ok',
            'application' => 'app-monitor',
            'server' => 'VM4-Application-Server-3',
            'probes_configured' => 3,
        ]);
        $this->assertSame(PHP_VERSION, $response->json('php'));
    }
}