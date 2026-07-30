<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Verifies that every public page answers with 200 and renders the content
 * read from config/project.php.
 *
 * These tests are executed by the "Test" stage of the Jenkins pipeline, before
 * the Docker image is built: a failing test stops the deployment.
 */
class PagesTest extends TestCase
{
    /**
     * Every public route, with a fragment of text it must contain.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function pageProvider(): array
    {
        return [
            'home' => ['/', 'CI/CD pipeline'],
            'infrastructure' => ['/infrastructure', 'Ubuntu Server'],
            'pipeline' => ['/pipeline', 'Jenkinsfile'],
            'technologies' => ['/technologies', 'Ansible'],
            'monitoring' => ['/monitoring', 'Prometheus'],
        ];
    }

    #[DataProvider('pageProvider')]
    public function test_public_pages_respond_with_200(string $uri, string $expectedText): void
    {
        $response = $this->get($uri);

        $response->assertOk();
        $response->assertSee($expectedText, false);
    }

    public function test_the_home_page_lists_all_nine_applications(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        foreach (config('project.servers') as $server) {
            foreach ($server['apps'] as $app) {
                $response->assertSee($app['name']);
            }
        }
    }

    public function test_the_infrastructure_page_shows_the_php_versions(): void
    {
        $response = $this->get('/infrastructure');

        $response->assertOk();
        $response->assertSee('php:8.3-fpm');
        $response->assertSee('php:7.4-fpm');
    }

    public function test_the_navigation_is_present_on_every_page(): void
    {
        foreach (config('project.navigation') as $item) {
            $response = $this->get(route($item['route']));

            $response->assertOk();
            $response->assertSee($item['label'], false);
        }
    }

    public function test_an_unknown_route_returns_404(): void
    {
        $this->get('/this-route-does-not-exist')->assertNotFound();
    }
}
