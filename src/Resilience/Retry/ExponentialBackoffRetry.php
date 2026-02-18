<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Resilience\Retry;

use Kkxdev\PhonePe\Contracts\RetryPolicyInterface;
use Kkxdev\PhonePe\Exceptions\ApiException;
use Kkxdev\PhonePe\Exceptions\NetworkException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Exponential Backoff Retry Policy
 *
 * Retries failed operations with exponentially increasing delays.
 */
final class ExponentialBackoffRetry implements RetryPolicyInterface
{
    public function __construct(
        private readonly int $maxAttempts,
        private readonly int $baseDelayMs,
        private readonly int $maxDelayMs,
        private readonly bool $jitter,
        private readonly LoggerInterface $logger = new NullLogger()
    ) {}

    /**
     * {@inheritDoc}
     */
    public function execute(callable $operation): mixed
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxAttempts) {
            try {
                return $operation();
            } catch (\Throwable $e) {
                $lastException = $e;

                if (!$this->shouldRetry($e) || $attempt === $this->maxAttempts - 1) {
                    throw $e;
                }

                $delay = $this->getDelay($attempt);

                $this->logger->warning('Operation failed, retrying...', [
                    'attempt' => $attempt + 1,
                    'max_attempts' => $this->maxAttempts,
                    'delay_ms' => $delay,
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]);

                usleep($delay * 1000); // Convert ms to microseconds
                $attempt++;
            }
        }

        throw $lastException;
    }

    /**
     * {@inheritDoc}
     */
    public function shouldRetry(\Throwable $exception): bool
    {
        // Retry on network errors
        if ($exception instanceof NetworkException) {
            return true;
        }

        // Retry on server errors (5xx)
        if ($exception instanceof ApiException && $exception->isServerError()) {
            return true;
        }

        // Don't retry client errors (4xx)
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getDelay(int $attempt): int
    {
        // Calculate exponential delay: baseDelay * 2^attempt
        $delay = $this->baseDelayMs * (2 ** $attempt);

        // Cap at max delay
        $delay = min($delay, $this->maxDelayMs);

        // Add jitter to prevent thundering herd
        if ($this->jitter) {
            $jitterRange = (int) ($delay * 0.2); // ±20% jitter
            $delay += random_int(-$jitterRange, $jitterRange);
        }

        return max($delay, 0);
    }

    /**
     * {@inheritDoc}
     */
    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }
}
