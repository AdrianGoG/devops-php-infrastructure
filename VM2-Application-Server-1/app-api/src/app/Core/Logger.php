<?php

declare(strict_types=1);

namespace App\Core;

/**
 * File logger that writes one JSON object per line.
 *
 * The format is deliberate: Logstash can ingest the file with a plain json
 * codec, without a custom grok pattern, so the API logs land in Elasticsearch
 * next to the NGINX logs of the infrastructure.
 */
class Logger
{
    const LEVELS = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3];

    /** @var string */
    private $path;

    /** @var int */
    private $threshold;

    public function __construct(string $path, string $level = 'info')
    {
        $this->path = $path;
        $this->threshold = isset(self::LEVELS[$level]) ? self::LEVELS[$level] : self::LEVELS['info'];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function log(string $level, string $message, array $context = []): void
    {
        $severity = isset(self::LEVELS[$level]) ? self::LEVELS[$level] : self::LEVELS['info'];

        if ($severity < $this->threshold) {
            return;
        }

        $entry = [
            'timestamp' => date('c'),
            'level' => $level,
            'application' => 'app-api',
            'server' => 'VM2-Application-Server-1',
            'php' => PHP_VERSION,
            'message' => $message,
        ];

        if ($context !== []) {
            $entry['context'] = $context;
        }

        $this->write((string) json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function write(string $line): void
    {
        $directory = dirname($this->path);

        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            // Never let logging break a response: if the directory cannot be
            // created the entry is dropped rather than fataling the request.
            return;
        }

        @file_put_contents($this->path, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
