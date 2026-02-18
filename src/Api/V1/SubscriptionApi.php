<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Api\V1;

use Kkxdev\PhonePe\Contracts\HttpClientInterface;
use Kkxdev\PhonePe\Contracts\SubscriptionApiInterface;
use Kkxdev\PhonePe\DTO\Subscription\OrderStatusResponse;
use Kkxdev\PhonePe\DTO\Subscription\SubscriptionSetupRequest;
use Kkxdev\PhonePe\DTO\Subscription\SubscriptionStatusResponse;
use Kkxdev\PhonePe\Support\EndpointBuilder;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Subscription API v1
 */
final class SubscriptionApi implements SubscriptionApiInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EndpointBuilder $endpointBuilder,
        private readonly AuthApi $authApi,
        private readonly LoggerInterface $logger = new NullLogger()
    ) {}

    /**
     * {@inheritDoc}
     */
    public function setup(SubscriptionSetupRequest $request): array
    {
        $url = $this->endpointBuilder->buildUrl($this->endpointBuilder->subscriptionSetup());

        $this->logger->info('Setting up subscription', [
            'merchant_order_id' => $request->merchantOrderId,
            'merchant_subscription_id' => $request->merchantSubscriptionId,
        ]);

        $response = $this->httpClient->send(
            'POST',
            $url,
            $this->getAuthHeaders(),
            $request->toArray()
        );

        $this->logger->info('Subscription setup successful', [
            'order_id' => $response['orderId'] ?? null,
            'state' => $response['state'] ?? null,
        ]);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderStatus(string $merchantOrderId, bool $includeDetails = true): OrderStatusResponse
    {
        $url = $this->endpointBuilder->buildUrl(
            $this->endpointBuilder->orderStatus($merchantOrderId, $includeDetails)
        );

        $this->logger->info('Fetching order status', ['merchant_order_id' => $merchantOrderId]);

        $response = $this->httpClient->send(
            'GET',
            $url,
            $this->getAuthHeaders()
        );

        return OrderStatusResponse::fromResponse($response);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus(string $merchantSubscriptionId): SubscriptionStatusResponse
    {
        $url = $this->endpointBuilder->buildUrl(
            $this->endpointBuilder->subscriptionStatus($merchantSubscriptionId)
        );

        $this->logger->info('Fetching subscription status', ['merchant_subscription_id' => $merchantSubscriptionId]);

        $response = $this->httpClient->send(
            'GET',
            $url,
            $this->getAuthHeaders()
        );

        return SubscriptionStatusResponse::fromResponse($response);
    }

    /**
     * {@inheritDoc}
     */
    public function cancel(string $merchantSubscriptionId): void
    {
        $url = $this->endpointBuilder->buildUrl(
            $this->endpointBuilder->subscriptionCancel($merchantSubscriptionId)
        );

        $this->logger->info('Cancelling subscription', ['merchant_subscription_id' => $merchantSubscriptionId]);

        $this->httpClient->send(
            'POST',
            $url,
            $this->getAuthHeaders(),
            []
        );

        $this->logger->info('Subscription cancelled successfully');
    }

    /**
     * Get authorization headers
     *
     * @return array<string, string>
     */
    private function getAuthHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => $this->authApi->getAuthorizationHeader(),
        ];
    }
}
