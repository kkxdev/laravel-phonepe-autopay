<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Exceptions;

/**
 * Authentication Exception
 *
 * Thrown when OAuth token fetch or validation fails.
 */
class AuthenticationException extends PhonePeException
{
    /**
     * Create exception for failed token fetch
     *
     * @param string $reason Failure reason
     * @param array<string, mixed> $context
     * @return static
     */
    public static function tokenFetchFailed(string $reason, array $context = []): static
    {
        return static::withContext(
            "Failed to fetch OAuth token: {$reason}",
            $context
        );
    }

    /**
     * Create exception for expired token
     *
     * @return static
     */
    public static function tokenExpired(): static
    {
        return new static('OAuth token has expired');
    }

    /**
     * Create exception for invalid credentials
     *
     * @return static
     */
    public static function invalidCredentials(): static
    {
        return new static('Invalid client credentials provided');
    }
}
