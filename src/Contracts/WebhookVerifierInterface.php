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
     * @throws \Auw\PhonePe\Exceptions\WebhookVerificationException On signature verification failure
     */
    public function verify(string $authHeader, array $payload): WebhookEvent;

    /**
     * Compute expected signature for payload
     *
     * @param string $username Webhook username
     * @param string $password Webhook password
     * @return string SHA256 hash
     */
    public function computeSignature(string $username, string $password): string;
}
