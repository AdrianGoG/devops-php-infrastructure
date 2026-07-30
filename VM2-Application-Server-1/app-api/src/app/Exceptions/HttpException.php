<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * An error that maps directly onto an HTTP status code.
 *
 * The kernel turns these into JSON responses; anything else becomes a 500 and
 * is written to the log for the ELK stack.
 */
class HttpException extends RuntimeException
{
    /** @var int */
    private $status;

    /** @var array<string, mixed> */
    private $details;

    /**
     * @param array<string, mixed> $details
     */
    public function __construct(string $message, int $status = 500, array $details = [])
    {
        parent::__construct($message, $status);

        $this->status = $status;
        $this->details = $details;
    }

    public function status(): int
    {
        return $this->status;
    }

    /**
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return $this->details;
    }
}
