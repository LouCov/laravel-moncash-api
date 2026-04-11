<?php

namespace LouCov\LaravelMonCashApi\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Base exception for every error raised by the MonCash package.
 */
class MoncashException extends RuntimeException
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        protected array $context = []
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }
}
