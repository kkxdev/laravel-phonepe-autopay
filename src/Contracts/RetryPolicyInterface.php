<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Contracts;

/**
 * Retry Policy Interface
 *
 * Defines retry behavior for failed HTTP requests.
 */
interface RetryPolicyInterface
{
    /**
     * Execute callable with retry logic
     *
     * @param callable $operation Operation to execute
     * @return mixed Operation result
     * @throws \Throwable Last exception if all retries exhausted
     */
    public function execute(callable $operation): mixed;

    /**
     * Check if exception should trigger a retry
     *
     * @param \Throwable $exception Exception to evaluate
     * @return bool True if should retry
     */
    public function shouldRetry(\Throwable $exception): bool;

    /**
     * Calculate delay before next retry attempt
     *
     * @param int $attempt Current attempt number (0-indexed)
     * @return int Delay in milliseconds
     */
    public function getDelay(int $attempt): int;

    /**
     * Get maximum number of retry attempts
     *
     * @return int Max attempts
     */
    public function getMaxAttempts(): int;
}
