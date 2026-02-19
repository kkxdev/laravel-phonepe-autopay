<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Api\V1;

use Kkxdev\PhonePe\Contracts\HttpClientInterface;
use Kkxdev\PhonePe\Contracts\RedemptionApiInterface;
use Kkxdev\PhonePe\DTO\Redemption\RedemptionExecuteRequest;
use Kkxdev\PhonePe\DTO\Redemption\RedemptionNotifyRequest;
use Kkxdev\PhonePe\DTO\Redemption\RedemptionStatusResponse;
use Kkxdev\PhonePe\Support\EndpointBuilder;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Redemption API v1
 */
final class RedemptionApi implements RedemptionApiInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private EndpointBuilder $endpointBuilder,
        private AuthApi $authApi,
        private LoggerInterface $logger = new NullLogger()
    ) {}

    /**
     * {@inheritDoc}
     */
    public function notify(RedemptionNotifyRequest $request): array
    {
        $url = $this->endpointBuilder->buildUrl($this->endpointBuilder->redemptionNotify());

        $this->logger->info('Notifying redemption', [
            'merchant_order_id' => $request->merchantOrderId,
            'merchant_subscription_id' => $request->merchantSubscriptionId,
            'amount' => $request->amount,
        ]);

        $response = $this->httpClient->send(
            'POST',
            $url,
            $this->getAuthHeaders(),
            $request->toArray()
        );

        $this->logger->info('Redemption notified successfully', [
            'order_id' => $response['orderId'] ?? null,
            'state' => $response['state'] ?? null,
        ]);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(RedemptionExecuteRequest $request): array
    {
        $url = $this->endpointBuilder->buildUrl($this->endpointBuilder->redemptionExecute());

        $this->logger->info('Executing redemption', [
            'merchant_order_id' => $request->merchantOrderId,
            'idempotency_key' => $request->idempotencyKey,
        ]);

        $headers = $this->getAuthHeaders();

        if ($request->idempotencyKey !== null) {
            $headers['X-Idempotency-Key'] = $request->idempotencyKey;
        }

        $response = $this->httpClient->send(
            'POST',
            $url,
            $headers,
            $request->toArray()
        );

        $this->logger->info('Redemption executed', [
            'state' => $response['state'] ?? null,
            'transaction_id' => $response['transactionId'] ?? null,
        ]);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus(string $merchantOrderId, bool $includeDetails = true): RedemptionStatusResponse
    {
        $url = $this->endpointBuilder->buildUrl(
            $this->endpointBuilder->redemptionOrderStatus($merchantOrderId, $includeDetails)
        );

        $this->logger->info('Fetching redemption status', ['merchant_order_id' => $merchantOrderId]);

        $response = $this->httpClient->send(
            'GET',
            $url,
            $this->getAuthHeaders()
        );

        return RedemptionStatusResponse::fromResponse($response);
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
