<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Exceptions;

/**
 * Webhook Verification Exception
 *
 * Thrown when webhook signature verification fails.
 */
class WebhookVerificationException extends PhonePeException
{
    /**
     * Create exception for signature mismatch
     *
     * @param string $expected
     * @param string $actual
     * @return static
     */
    public static function signatureMismatch(string $expected, string $actual): static
    {
        return static::withContext(
            'Webhook signature verification failed: signature mismatch',
            ['expected' => $expected, 'actual' => $actual]
        );
    }

    /**
     * Create exception for missing signature
     *
     * @return static
     */
    public static function missingSignature(): static
    {
        return new static('Webhook signature is missing from Authorization header');
    }

    /**
     * Create exception for invalid payload
     *
     * @param string $reason
     * @return static
     */
    public static function invalidPayload(string $reason): static
    {
        return new static("Invalid webhook payload: {$reason}");
    }
}
