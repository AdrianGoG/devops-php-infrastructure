<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Configuration read from the .env file of the application.
 *
 * There is no third-party dotenv dependency on purpose: the fewer runtime
 * dependencies a legacy service has, the fewer things can block a PHP upgrade.
 */
class Config
{
    /** @var array<string, string> */
    private $values = [];

    /**
     * @param array<string, string> $values
     */
    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    /**
     * Build the configuration from a .env file, falling back to the real
     * environment (which is how the values arrive inside the container and
     * during the test run).
     */
    public static function load(string $basePath): self
    {
        $values = [];

        $file = $basePath . '/.env';

        if (is_readable($file)) {
            $values = self::parse((string) file_get_contents($file));
        }

        // Real environment variables always win over the file.
        foreach (self::knownKeys() as $key) {
            $fromEnv = getenv($key);

            if ($fromEnv !== false && $fromEnv !== '') {
                $values[$key] = $fromEnv;
            }
        }

        return new self($values);
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return isset($this->values[$key]) ? $this->values[$key] : $default;
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->get($key);

        if ($value === null) {
            return $default;
        }

        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    public function int(string $key, int $default = 0): int
    {
        $value = $this->get($key);

        return $value === null ? $default : (int) $value;
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->values;
    }

    /**
     * Minimal .env parser: KEY=value, optional quotes, # comments.
     *
     * @return array<string, string>
     */
    private static function parse(string $contents): array
    {
        $values = [];

        foreach (preg_split('/\r\n|\r|\n/', $contents) as $line) {
            $line = trim($line);

            if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }

            list($key, $value) = explode('=', $line, 2);

            $key = trim($key);
            $value = trim($value);

            // Strip a single layer of surrounding quotes.
            if (strlen($value) > 1) {
                $first = substr($value, 0, 1);
                $last = substr($value, -1);

                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            $values[$key] = $value;
        }

        return $values;
    }

    /**
     * @return list<string>
     */
    private static function knownKeys(): array
    {
        return [
            'APP_NAME',
            'APP_ENV',
            'APP_DEBUG',
            'APP_URL',
            'API_KEY',
            'DB_HOST',
            'DB_PORT',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
            'LOG_PATH',
            'LOG_LEVEL',
        ];
    }
}
