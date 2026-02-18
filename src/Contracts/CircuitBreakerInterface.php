<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Contracts;

/**
 * Circuit Breaker Interface
 *
 * Implements circuit breaker pattern to prevent cascading failures.
 * States: CLOSED (normal) → OPEN (failing) → HALF_OPEN (testing recovery)
 */
interface CircuitBreakerInterface
{
    /**
     * Execute callable with circuit breaker protection
     *
     * @param callable $operation Operation to execute
     * @return mixed Operation result
     * @throws \Auw\PhonePe\Exceptions\CircuitBreakerException When circuit is open
     * @throws \Throwable Original exception from operation
     */
    public function execute(callable $operation): mixed;

    /**
     * Check if circuit breaker is open (failing)
     *
     * @return bool True if circuit is open
     */
    public function isOpen(): bool;

    /**
     * Check if circuit breaker is closed (normal)
     *
     * @return bool True if circuit is closed
     */
    public function isClosed(): bool;

    /**
     * Check if circuit breaker is half-open (testing recovery)
     *
     * @return bool True if circuit is half-open
     */
    public function isHalfOpen(): bool;

    /**
     * Record successful operation
     *
     * @return void
     */
    public function recordSuccess(): void;

    /**
     * Record failed operation
     *
     * @return void
     */
    public function recordFailure(): void;

    /**
     * Reset circuit breaker to closed state
     *
     * @return void
     */
    public function reset(): void;
}
