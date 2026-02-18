<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Exceptions;

use Exception;

/**
 * Base exception for all PhonePe SDK errors
 */
class PhonePeException extends Exception
{
    protected array $context = [];

    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get exception context data
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Create exception with context
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return static
     */
    public static function withContext(string $message, array $context = []): static
    {
        return new static($message, 0, null, $context);
    }
}
