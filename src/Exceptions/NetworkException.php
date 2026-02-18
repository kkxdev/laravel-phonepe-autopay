<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Exceptions;

/**
 * Network Exception
 *
 * Thrown when network connectivity issues occur (timeouts, DNS failures, etc.).
 */
class NetworkException extends PhonePeException
{
    /**
     * Create exception for connection timeout
     *
     * @param string $url
     * @param int $timeout
     * @return static
     */
    public static function connectionTimeout(string $url, int $timeout): static
    {
        return static::withContext(
            "Connection to {$url} timed out after {$timeout} seconds",
            ['url' => $url, 'timeout' => $timeout]
        );
    }

    /**
     * Create exception for request timeout
     *
     * @param string $url
     * @param int $timeout
     * @return static
     */
    public static function requestTimeout(string $url, int $timeout): static
    {
        return static::withContext(
            "Request to {$url} timed out after {$timeout} seconds",
            ['url' => $url, 'timeout' => $timeout]
        );
    }

    /**
     * Create exception for connection failure
     *
     * @param string $url
     * @param string $reason
     * @return static
     */
    public static function connectionFailed(string $url, string $reason): static
    {
        return static::withContext(
            "Failed to connect to {$url}: {$reason}",
            ['url' => $url, 'reason' => $reason]
        );
    }
}
