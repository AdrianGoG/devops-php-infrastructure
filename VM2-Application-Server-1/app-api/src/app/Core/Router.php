<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\HttpException;

/**
 * A small regex based router.
 *
 * Patterns support {name} placeholders, for example /api/applications/{name}.
 * Handlers are [ControllerClass, 'method'] pairs; the controller receives the
 * service container and is instantiated only when its route matches.
 */
class Router
{
    /** @var list<array{method: string, pattern: string, regex: string, handler: array{0: class-string, 1: string}}> */
    private $routes = [];

    /**
     * @param array{0: class-string, 1: string} $handler
     */
    public function get(string $pattern, array $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    /**
     * @param array{0: class-string, 1: string} $handler
     */
    public function post(string $pattern, array $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    /**
     * @param array{0: class-string, 1: string} $handler
     */
    public function add(string $method, string $pattern, array $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'regex' => $this->toRegex($pattern),
            'handler' => $handler,
        ];
    }

    /**
     * Resolve the request and run the matching handler.
     *
     * @throws HttpException when nothing matches the URI (404) or when the URI
     *                       exists but not for this method (405).
     */
    public function dispatch(Request $request, Container $container): Response
    {
        $pathMatched = false;

        foreach ($this->routes as $route) {
            if (preg_match($route['regex'], $request->path(), $matches) !== 1) {
                continue;
            }

            $pathMatched = true;

            if ($route['method'] !== $request->method()) {
                continue;
            }

            $parameters = [];

            foreach ($matches as $key => $value) {
                if (!is_int($key)) {
                    $parameters[$key] = rawurldecode((string) $value);
                }
            }

            list($class, $method) = $route['handler'];

            $controller = new $class($container);

            return $controller->{$method}($request, $parameters);
        }

        if ($pathMatched) {
            throw new HttpException('The ' . $request->method() . ' method is not supported for this endpoint.', 405);
        }

        throw new HttpException('Endpoint ' . $request->path() . ' does not exist.', 404);
    }

    /**
     * @return list<array{method: string, pattern: string}>
     */
    public function definitions(): array
    {
        $definitions = [];

        foreach ($this->routes as $route) {
            $definitions[] = ['method' => $route['method'], 'pattern' => $route['pattern']];
        }

        return $definitions;
    }

    /**
     * Turn /api/applications/{name} into a named capture group regex.
     */
    private function toRegex(string $pattern): string
    {
        $quoted = preg_quote($pattern, '#');

        // preg_quote escapes the braces, so match the escaped form here.
        $regex = preg_replace('#\\\\\{([a-zA-Z_]+)\\\\\}#', '(?P<$1>[^/]+)', $quoted);

        return '#^' . $regex . '$#';
    }
}
