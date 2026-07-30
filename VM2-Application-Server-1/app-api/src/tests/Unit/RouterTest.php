<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Config;
use App\Core\Container;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Exceptions\HttpException;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private function container(): Container
    {
        return new Container(
            dirname(__DIR__, 2),
            new Config(['APP_ENV' => 'testing']),
            new Logger('storage/logs/testing.log', 'error')
        );
    }

    public function test_it_matches_a_static_route(): void
    {
        $router = new Router();
        $router->get('/api/servers', [RouterTestController::class, 'plain']);

        $response = $router->dispatch(Request::create('GET', '/api/servers'), $this->container());

        $this->assertSame(200, $response->status());
        $this->assertSame(['handled' => 'plain'], $response->decoded());
    }

    public function test_it_extracts_named_parameters(): void
    {
        $router = new Router();
        $router->get('/api/applications/{name}', [RouterTestController::class, 'withParameters']);

        $response = $router->dispatch(Request::create('GET', '/api/applications/app-api'), $this->container());

        $this->assertSame(['name' => 'app-api'], $response->decoded());
    }

    public function test_it_decodes_url_encoded_parameters(): void
    {
        $router = new Router();
        $router->get('/api/applications/{name}', [RouterTestController::class, 'withParameters']);

        $response = $router->dispatch(Request::create('GET', '/api/applications/app%2Dapi'), $this->container());

        $this->assertSame(['name' => 'app-api'], $response->decoded());
    }

    public function test_a_parameter_never_spans_a_slash(): void
    {
        $router = new Router();
        $router->get('/api/applications/{name}', [RouterTestController::class, 'withParameters']);

        $this->expectException(HttpException::class);

        $router->dispatch(Request::create('GET', '/api/applications/app-api/extra'), $this->container());
    }

    public function test_an_unknown_path_throws_a_404(): void
    {
        $router = new Router();
        $router->get('/api/servers', [RouterTestController::class, 'plain']);

        try {
            $router->dispatch(Request::create('GET', '/api/nope'), $this->container());
            $this->fail('A 404 was expected.');
        } catch (HttpException $exception) {
            $this->assertSame(404, $exception->status());
        }
    }

    public function test_a_known_path_with_the_wrong_method_throws_a_405(): void
    {
        $router = new Router();
        $router->get('/api/servers', [RouterTestController::class, 'plain']);

        try {
            $router->dispatch(Request::create('POST', '/api/servers'), $this->container());
            $this->fail('A 405 was expected.');
        } catch (HttpException $exception) {
            $this->assertSame(405, $exception->status());
        }
    }

    public function test_it_reports_its_definitions(): void
    {
        $router = new Router();
        $router->get('/api/servers', [RouterTestController::class, 'plain']);
        $router->post('/api/deployments', [RouterTestController::class, 'plain']);

        $this->assertSame(
            [
                ['method' => 'GET', 'pattern' => '/api/servers'],
                ['method' => 'POST', 'pattern' => '/api/deployments'],
            ],
            $router->definitions()
        );
    }
}

/**
 * Test double: a controller with the same constructor contract as the real ones.
 */
class RouterTestController
{
    public function __construct(Container $container)
    {
        unset($container);
    }

    /**
     * @param array<string, string> $parameters
     */
    public function plain(Request $request, array $parameters = []): Response
    {
        unset($request, $parameters);

        return Response::json(['handled' => 'plain']);
    }

    /**
     * @param array<string, string> $parameters
     */
    public function withParameters(Request $request, array $parameters = []): Response
    {
        unset($request);

        return Response::json($parameters);
    }
}
