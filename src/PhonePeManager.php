<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe;

use Kkxdev\PhonePe\Api\V1\AuthApi;
use Kkxdev\PhonePe\Api\V1\RedemptionApi;
use Kkxdev\PhonePe\Api\V1\RefundApi;
use Kkxdev\PhonePe\Api\V1\SubscriptionApi;
use Kkxdev\PhonePe\Contracts\AuthApiInterface;
use Kkxdev\PhonePe\Contracts\RedemptionApiInterface;
use Kkxdev\PhonePe\Contracts\RefundApiInterface;
use Kkxdev\PhonePe\Contracts\SubscriptionApiInterface;
use Kkxdev\PhonePe\Contracts\WebhookVerifierInterface;
use Kkxdev\PhonePe\DTO\Webhook\WebhookEvent;
use Kkxdev\PhonePe\Support\EnvironmentResolver;
use InvalidArgumentException;

/**
 * PhonePe Manager
 *
 * Main entry point for PhonePe SDK. Provides access to all API resources.
 */
final class PhonePeManager
{
    public function __construct(
        private AuthApiInterface $authApi,
        private SubscriptionApiInterface $subscriptionApi,
        private RedemptionApiInterface $redemptionApi,
        private RefundApiInterface $refundApi,
        private WebhookVerifierInterface $webhookVerifier,
        private EnvironmentResolver $environmentResolver,
        private string $version = 'v1'
    ) {}

    /**
     * Get Authentication API
     *
     * @return AuthApiInterface
     */
    public function auth(): AuthApiInterface
    {
        return $this->authApi;
    }

    /**
     * Get Subscription API
     *
     * @return SubscriptionApiInterface
     */
    public function subscription(): SubscriptionApiInterface
    {
        return $this->subscriptionApi;
    }

    /**
     * Get Redemption API
     *
     * @return RedemptionApiInterface
     */
    public function redemption(): RedemptionApiInterface
    {
        return $this->redemptionApi;
    }

    /**
     * Get Refund API
     *
     * @return RefundApiInterface
     */
    public function refund(): RefundApiInterface
    {
        return $this->refundApi;
    }

    /**
     * Get Webhook Verifier
     *
     * @return WebhookVerifierInterface
     */
    public function webhook(): WebhookVerifierInterface
    {
        return $this->webhookVerifier;
    }

    /**
     * Get API version
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Verify and parse webhook payload
     *
     * @param string $authHeader
     * @param array<string, mixed> $payload
     * @return WebhookEvent
     */
    public function verifyWebhook(string $authHeader, array $payload): WebhookEvent
    {
        return $this->webhookVerifier->verify($authHeader, $payload);
    }

    /**
     * Get success redirect URL for current environment
     *
     * @return string
     */
    public function getSuccessUrl(): string
    {
        return $this->environmentResolver->getSuccessUrl();
    }

    /**
     * Get failure redirect URL for current environment
     *
     * @return string
     */
    public function getFailureUrl(): string
    {
        return $this->environmentResolver->getFailureUrl();
    }
}
