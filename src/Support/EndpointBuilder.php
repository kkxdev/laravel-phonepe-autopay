<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Support;

/**
 * Endpoint Builder
 *
 * Constructs PhonePe API endpoint URLs.
 */
final class EndpointBuilder
{
    public function __construct(
        private EnvironmentResolver $environment
    ) {}

    /**
     * Build OAuth token endpoint
     *
     * @return string
     */
    public function authToken(): string
    {
        return sprintf(
            '/%s/v1/oauth/token',
            $this->environment->getOAuthPrefix()
        );
    }

    /**
     * Build subscription setup endpoint
     *
     * @return string
     */
    public function subscriptionSetup(): string
    {
        return sprintf(
            '/%s/checkout/v2/pay',
            $this->environment->getPgPrefix()
        );
    }

    /**
     * Build order status endpoint
     *
     * @param string $merchantOrderId
     * @param bool $includeDetails
     * @return string
     */
    public function orderStatus(string $merchantOrderId, bool $includeDetails = true): string
    {
        $endpoint = sprintf(
            '/%s/checkout/v2/order/%s/status',
            $this->environment->getPgPrefix(),
            $merchantOrderId
        );

        return $includeDetails ? "{$endpoint}?details=true" : $endpoint;
    }

    /**
     * Build subscription status endpoint
     *
     * @param string $merchantSubscriptionId
     * @return string
     */
    public function subscriptionStatus(string $merchantSubscriptionId): string
    {
        return sprintf(
            '/%s/subscriptions/v2/%s/status?details=true',
            $this->environment->getPgPrefix(),
            $merchantSubscriptionId
        );
    }

    /**
     * Build subscription cancel endpoint
     *
     * @param string $merchantSubscriptionId
     * @return string
     */
    public function subscriptionCancel(string $merchantSubscriptionId): string
    {
        return sprintf(
            '/%s/subscriptions/v2/%s/cancel',
            $this->environment->getPgPrefix(),
            $merchantSubscriptionId
        );
    }

    /**
     * Build redemption notify endpoint
     *
     * @return string
     */
    public function redemptionNotify(): string
    {
        return sprintf(
            '/%s/subscriptions/v2/notify',
            $this->environment->getPgPrefix()
        );
    }

    /**
     * Build redemption execute endpoint
     *
     * @return string
     */
    public function redemptionExecute(): string
    {
        return sprintf(
            '/%s/subscriptions/v2/redeem',
            $this->environment->getPgPrefix()
        );
    }

    /**
     * Build redemption order status endpoint
     *
     * @param string $merchantOrderId
     * @param bool $includeDetails
     * @return string
     */
    public function redemptionOrderStatus(string $merchantOrderId, bool $includeDetails = true): string
    {
        $endpoint = sprintf(
            '/%s/subscriptions/v2/order/%s/status',
            $this->environment->getPgPrefix(),
            $merchantOrderId
        );

        return $includeDetails ? "{$endpoint}?details=true" : $endpoint;
    }

    /**
     * Build refund endpoint
     *
     * @return string
     */
    public function refund(): string
    {
        return sprintf(
            '/%s/payments/v2/refund',
            $this->environment->getPgPrefix()
        );
    }

    /**
     * Build refund status endpoint
     *
     * @param string $merchantRefundId
     * @return string
     */
    public function refundStatus(string $merchantRefundId): string
    {
        return sprintf(
            '/%s/payments/v2/refund/%s/status',
            $this->environment->getPgPrefix(),
            $merchantRefundId
        );
    }

    /**
     * Build full URL
     *
     * @param string $endpoint
     * @return string
     */
    public function buildUrl(string $endpoint): string
    {
        return $this->environment->getBaseUrl() . $endpoint;
    }
}
