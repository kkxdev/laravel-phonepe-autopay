<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Exceptions;

/**
 * Circuit Breaker Exception
 *
 * Thrown when circuit breaker is open (preventing requests to failing service).
 */
class CircuitBreakerException extends PhonePeException
{
    /**
     * Create exception for open circuit
     *
     * @param int $cooldownSeconds Seconds until circuit may close
     * @return static
     */
    public static function circuitOpen(int $cooldownSeconds): static
    {
        return static::withContext(
            "Circuit breaker is OPEN. Service temporarily unavailable. Retry after {$cooldownSeconds} seconds.",
            ['cooldown_seconds' => $cooldownSeconds]
        );
    }
}
