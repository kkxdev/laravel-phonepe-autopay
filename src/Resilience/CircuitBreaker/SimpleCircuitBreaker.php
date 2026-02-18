<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Resilience\CircuitBreaker;

use Kkxdev\PhonePe\Contracts\CircuitBreakerInterface;
use Kkxdev\PhonePe\Exceptions\CircuitBreakerException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Simple Circuit Breaker Implementation
 *
 * Protects against cascading failures by preventing requests to failing services.
 */
final class SimpleCircuitBreaker implements CircuitBreakerInterface
{
    private const CACHE_KEY_STATE = 'phonepe_circuit_breaker_state';
    private const CACHE_KEY_FAILURES = 'phonepe_circuit_breaker_failures';
    private const CACHE_KEY_LAST_FAILURE = 'phonepe_circuit_breaker_last_failure';
    private const CACHE_KEY_SUCCESSES = 'phonepe_circuit_breaker_successes';

    public function __construct(
        private readonly CacheRepository $cache,
        private readonly int $failureThreshold,
        private readonly int $successThreshold,
        private readonly int $cooldownSeconds,
        private readonly LoggerInterface $logger = new NullLogger()
    ) {}

    /**
     * {@inheritDoc}
     */
    public function execute(callable $operation): mixed
    {
        if ($this->isOpen()) {
            $timeSinceLastFailure = time() - $this->getLastFailureTime();

            if ($timeSinceLastFailure < $this->cooldownSeconds) {
                $remaining = $this->cooldownSeconds - $timeSinceLastFailure;
                throw CircuitBreakerException::circuitOpen($remaining);
            }

            // Transition to half-open
            $this->transitionToHalfOpen();
        }

        try {
            $result = $operation();
            $this->recordSuccess();
            return $result;
        } catch (\Throwable $e) {
            $this->recordFailure();
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isOpen(): bool
    {
        return $this->getState() === CircuitBreakerState::OPEN;
    }

    /**
     * {@inheritDoc}
     */
    public function isClosed(): bool
    {
        return $this->getState() === CircuitBreakerState::CLOSED;
    }

    /**
     * {@inheritDoc}
     */
    public function isHalfOpen(): bool
    {
        return $this->getState() === CircuitBreakerState::HALF_OPEN;
    }

    /**
     * {@inheritDoc}
     */
    public function recordSuccess(): void
    {
        $state = $this->getState();

        if ($state === CircuitBreakerState::HALF_OPEN) {
            $successes = $this->incrementSuccesses();

            if ($successes >= $this->successThreshold) {
                $this->transitionToClosed();
                $this->logger->info('Circuit breaker transitioned to CLOSED');
            }
        } elseif ($state === CircuitBreakerState::CLOSED) {
            // Reset failure count on success
            $this->resetFailures();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function recordFailure(): void
    {
        $failures = $this->incrementFailures();
        $this->cache->put(self::CACHE_KEY_LAST_FAILURE, time(), 3600);

        if ($failures >= $this->failureThreshold) {
            $this->transitionToOpen();
            $this->logger->warning('Circuit breaker transitioned to OPEN', [
                'failures' => $failures,
                'threshold' => $this->failureThreshold,
            ]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function reset(): void
    {
        $this->transitionToClosed();
    }

    /**
     * Get current state
     *
     * @return CircuitBreakerState
     */
    private function getState(): CircuitBreakerState
    {
        $state = $this->cache->get(self::CACHE_KEY_STATE);
        return $state ? CircuitBreakerState::from($state) : CircuitBreakerState::CLOSED;
    }

    /**
     * Transition to CLOSED state
     *
     * @return void
     */
    private function transitionToClosed(): void
    {
        $this->cache->put(self::CACHE_KEY_STATE, CircuitBreakerState::CLOSED->value, 3600);
        $this->resetFailures();
        $this->resetSuccesses();
    }

    /**
     * Transition to OPEN state
     *
     * @return void
     */
    private function transitionToOpen(): void
    {
        $this->cache->put(self::CACHE_KEY_STATE, CircuitBreakerState::OPEN->value, 3600);
        $this->resetSuccesses();
    }

    /**
     * Transition to HALF_OPEN state
     *
     * @return void
     */
    private function transitionToHalfOpen(): void
    {
        $this->cache->put(self::CACHE_KEY_STATE, CircuitBreakerState::HALF_OPEN->value, 3600);
        $this->resetSuccesses();
        $this->logger->info('Circuit breaker transitioned to HALF_OPEN');
    }

    /**
     * Increment failure count
     *
     * @return int New count
     */
    private function incrementFailures(): int
    {
        return (int) $this->cache->increment(self::CACHE_KEY_FAILURES, 1);
    }

    /**
     * Increment success count
     *
     * @return int New count
     */
    private function incrementSuccesses(): int
    {
        return (int) $this->cache->increment(self::CACHE_KEY_SUCCESSES, 1);
    }

    /**
     * Reset failure count
     *
     * @return void
     */
    private function resetFailures(): void
    {
        $this->cache->forget(self::CACHE_KEY_FAILURES);
    }

    /**
     * Reset success count
     *
     * @return void
     */
    private function resetSuccesses(): void
    {
        $this->cache->forget(self::CACHE_KEY_SUCCESSES);
    }

    /**
     * Get last failure timestamp
     *
     * @return int
     */
    private function getLastFailureTime(): int
    {
        return (int) $this->cache->get(self::CACHE_KEY_LAST_FAILURE, 0);
    }
}
