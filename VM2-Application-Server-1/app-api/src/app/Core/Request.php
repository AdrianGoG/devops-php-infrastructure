<?php

declare(strict_types=1);

namespace App\Core;

/**
 * An incoming HTTP request.
 *
 * Built either from the PHP superglobals (production) or explicitly through
 * create() (tests, CLI checks).
 */
class Request
{
    /** @var string */
    private $method;

    /** @var string */
    private $path;

    /** @var array<string, string> */
    private $query;

    /** @var array<string, string> */
    private $headers;

    /** @var string */
    private $body;

    /**
     * @param array<string, string> $query
     * @param array<string, string> $headers
     */
    public function __construct(string $method, string $path, array $query = [], array $headers = [], string $body = '')
    {
        $this->method = strtoupper($method);
        $this->path = $this->normalisePath($path);
        $this->query = $query;
        $this->headers = $this->normaliseHeaders($headers);
        $this->body = $body;
    }

    public static function fromGlobals(): self
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
        $path = (string) parse_url($uri, PHP_URL_PATH);

        $method = isset($_SERVER['REQUEST_METHOD']) ? (string) $_SERVER['REQUEST_METHOD'] : 'GET';

        $body = (string) file_get_contents('php://input');

        return new self($method, $path, self::stringMap($_GET), self::headersFromServer($_SERVER), $body);
    }

    /**
     * @param array<string, string> $query
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $json
     */
    public static function create(string $method, string $path, array $query = [], array $headers = [], ?array $json = null): self
    {
        $body = '';

        if ($json !== null) {
            $body = (string) json_encode($json);
            $headers['Content-Type'] = 'application/json';
        }

        return new self($method, $path, $query, $headers, $body);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function query(string $key, ?string $default = null): ?string
    {
        return isset($this->query[$key]) ? $this->query[$key] : $default;
    }

    /**
     * @return array<string, string>
     */
    public function allQuery(): array
    {
        return $this->query;
    }

    public function header(string $name, ?string $default = null): ?string
    {
        $key = strtolower($name);

        return isset($this->headers[$key]) ? $this->headers[$key] : $default;
    }

    /**
     * Decoded JSON payload of the request.
     *
     * @return array<string, mixed>
     */
    public function json(): array
    {
        if ($this->body === '') {
            return [];
        }

        $decoded = json_decode($this->body, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function body(): string
    {
        return $this->body;
    }

    private function normalisePath(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    /**
     * @param array<string, string> $headers
     * @return array<string, string>
     */
    private function normaliseHeaders(array $headers): array
    {
        $normalised = [];

        foreach ($headers as $name => $value) {
            $normalised[strtolower((string) $name)] = (string) $value;
        }

        return $normalised;
    }

    /**
     * Rebuild the request headers from $_SERVER (HTTP_* entries).
     *
     * @param array<string, mixed> $server
     * @return array<string, string>
     */
    private static function headersFromServer(array $server): array
    {
        $headers = [];

        foreach ($server as $key => $value) {
            $key = (string) $key;

            if (strpos($key, 'HTTP_') !== 0) {
                continue;
            }

            $name = str_replace('_', '-', strtolower(substr($key, 5)));
            $headers[$name] = (string) $value;
        }

        return $headers;
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, string>
     */
    private static function stringMap(array $input): array
    {
        $output = [];

        foreach ($input as $key => $value) {
            if (is_scalar($value)) {
                $output[(string) $key] = (string) $value;
            }
        }

        return $output;
    }
}
