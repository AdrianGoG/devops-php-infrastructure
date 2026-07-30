<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\HttpException;
use Throwable;

/**
 * Boots the application and turns a Request into a Response.
 *
 * The kernel is the only place that knows about error handling, so both the web
 * front controller and the test suite go through exactly the same path.
 */
class Kernel
{
    /** @var Container */
    private $container;

    /** @var Router */
    private $router;

    public function __construct(string $basePath, ?Config $config = null)
    {
        $config = $config !== null ? $config : Config::load($basePath);

        $logger = new Logger(
            $basePath . '/' . (string) $config->get('LOG_PATH', 'storage/logs/api.log'),
            (string) $config->get('LOG_LEVEL', 'info')
        );

        $this->container = new Container($basePath, $config, $logger);

        // Deprecation notices must never leak into a JSON response body: the
        // legacy constructs of this service produce them on every request.
        // They are still written to the log for the ELK stack.
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
        ini_set('display_errors', $config->bool('APP_DEBUG') ? '1' : '0');
        ini_set('log_errors', '1');

        $this->router = new Router();

        $register = require $basePath . '/routes/api.php';
        $register($this->router);
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function handle(Request $request): Response
    {
        $startedAt = microtime(true);

        try {
            $response = $this->router->dispatch($request, $this->container);
        } catch (HttpException $exception) {
            $response = Response::error($exception->getMessage(), $exception->status(), $exception->details());
        } catch (Throwable $exception) {
            // Anything unexpected - including the PHP 8 fallout documented in
            // MIGRATION.md - is logged with its origin and reported as a 500.
            $this->container->logger()->error($exception->getMessage(), [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'path' => $request->path(),
            ]);

            $response = Response::error(
                $this->container->config()->bool('APP_DEBUG')
                    ? $exception->getMessage()
                    : 'The API encountered an internal error.',
                500
            );
        }

        $this->container->logger()->info('request handled', [
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->status(),
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ]);

        return $response;
    }
}
