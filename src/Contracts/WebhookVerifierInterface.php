<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Contracts;

use Kkxdev\PhonePe\DTO\Webhook\WebhookEvent;

/**
 * Webhook Verifier Interface
 *
 * Verifies and parses PhonePe webhook callbacks.
 */
interface WebhookVerifierInterface
{
    /**
     * Verify webhook signature and parse event
     *
     * @param string $authHeader Authorization header value
     * @param array<string, mixed> $payload Webhook payload
     * @return WebhookEvent Parsed and validated webhook event
     * @throws \KKxdev\PhonePe\Exceptions\WebhookVerificationException On signature verification failure
     */
    public function verify(string $authHeader, array $payload): WebhookEvent;

    /**
     * Verify webhook signature without throwing an exception
     *
     * @param string $authHeader Authorization header value
     * @param array<string, mixed> $payload Webhook payload
     * @return bool
     */
    public function isValid(string $authHeader, array $payload): bool;

    /**
     * Compute expected signature for payload
     *
     * Algorithm: SHA256("{username}:{password}")
     *
     * @param string $username Webhook username
     * @param string $password Webhook password
     * @return string SHA256 hex hash
     */
    public function computeSignature(string $username, string $password): string;
}
