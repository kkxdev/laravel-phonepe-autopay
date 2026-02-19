<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Api\V1;

use Kkxdev\PhonePe\Contracts\HttpClientInterface;
use Kkxdev\PhonePe\Contracts\RefundApiInterface;
use Kkxdev\PhonePe\DTO\Refund\RefundRequest;
use Kkxdev\PhonePe\DTO\Refund\RefundStatusResponse;
use Kkxdev\PhonePe\Support\EndpointBuilder;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Refund API v1
 */
final class RefundApi implements RefundApiInterface
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
    public function create(RefundRequest $request): array
    {
        $url = $this->endpointBuilder->buildUrl($this->endpointBuilder->refund());

        $this->logger->info('Creating refund', [
            'merchant_refund_id' => $request->merchantRefundId,
            'original_merchant_order_id' => $request->originalMerchantOrderId,
            'amount' => $request->amount,
        ]);

        $response = $this->httpClient->send(
            'POST',
            $url,
            $this->getAuthHeaders(),
            $request->toArray()
        );

        $this->logger->info('Refund created successfully', [
            'refund_id' => $response['refundId'] ?? null,
            'state' => $response['state'] ?? null,
        ]);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus(string $merchantRefundId): RefundStatusResponse
    {
        $url = $this->endpointBuilder->buildUrl(
            $this->endpointBuilder->refundStatus($merchantRefundId)
        );

        $this->logger->info('Fetching refund status', ['merchant_refund_id' => $merchantRefundId]);

        $response = $this->httpClient->send(
            'GET',
            $url,
            $this->getAuthHeaders()
        );

        return RefundStatusResponse::fromResponse($response);
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
