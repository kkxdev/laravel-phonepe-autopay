<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Support;

use Kkxdev\PhonePe\Contracts\WebhookVerifierInterface;
use Kkxdev\PhonePe\DTO\Webhook\WebhookEvent;
use Kkxdev\PhonePe\Exceptions\WebhookVerificationException;

/**
 * Webhook Verifier
 *
 * Verifies PhonePe webhook signatures and parses events.
 */
final class WebhookVerifier implements WebhookVerifierInterface
{
    public function __construct(
        private $username,
        private $password
    ) {}

    /**
     * {@inheritDoc}
     */
    public function verify(string $authHeader, array $payload): WebhookEvent
    {
        if (empty($authHeader)) {
            throw WebhookVerificationException::missingSignature();
        }

        $expectedSignature = $this->computeSignature($this->username, $this->password);

        if (!hash_equals($expectedSignature, $authHeader)) {
            throw WebhookVerificationException::signatureMismatch($expectedSignature, $authHeader);
        }

        return WebhookEvent::fromPayload($payload);
    }

    /**
     * {@inheritDoc}
     */
    public function computeSignature(string $username, string $password): string
    {
        return hash('sha256', "{$username}:{$password}");
    }

    /**
     * Verify without throwing exception
     *
     * @param string $authHeader
     * @param array<string, mixed> $payload
     * @return bool
     */
    public function isValid(string $authHeader, array $payload): bool
    {
        try {
            $this->verify($authHeader, $payload);
            return true;
        } catch (WebhookVerificationException) {
            return false;
        }
    }
}
