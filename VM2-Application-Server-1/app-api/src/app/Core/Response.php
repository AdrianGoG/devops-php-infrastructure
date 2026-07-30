<?php

declare(strict_types=1);

namespace App\Core;

/**
 * An outgoing HTTP response.
 *
 * Responses are values, not side effects: the router returns them and only
 * send() touches the output buffer. That is what makes the endpoints testable
 * without a web server.
 */
class Response
{
    /** @var int */
    private $status;

    /** @var string */
    private $body;

    /** @var array<string, string> */
    private $headers;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(int $status, string $body, array $headers = [])
    {
        $this->status = $status;
        $this->body = $body;
        $this->headers = $headers;
    }

    /**
     * @param array<string, mixed>|list<mixed> $data
     */
    public static function json(array $data, int $status = 200): self
    {
        $body = (string) json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return new self($status, $body, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    /**
     * Plain text - used by the Prometheus exposition endpoint.
     */
    public static function text(string $body, int $status = 200): self
    {
        return new self($status, $body, ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8']);
    }

    /**
     * @param array<string, mixed> $extra
     */
    public static function error(string $message, int $status, array $extra = []): self
    {
        $payload = ['error' => ['status' => $status, 'message' => $message]];

        if ($extra !== []) {
            $payload['error'] = array_merge($payload['error'], $extra);
        }

        return self::json($payload, $status);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function body(): string
    {
        return $this->body;
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Decoded body - a convenience for the test suite.
     *
     * @return array<string, mixed>
     */
    public function decoded(): array
    {
        $decoded = json_decode($this->body, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->status);

            foreach ($this->headers as $name => $value) {
                header($name . ': ' . $value);
            }
        }

        echo $this->body;
    }
}
