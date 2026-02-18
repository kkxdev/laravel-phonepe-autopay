<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Contracts;

use Kkxdev\PhonePe\DTO\Refund\RefundRequest;
use Kkxdev\PhonePe\DTO\Refund\RefundStatusResponse;

/**
 * Refund API Interface
 *
 * Manages refund creation and status checking.
 */
interface RefundApiInterface
{
    /**
     * Create a refund for a completed transaction
     *
     * @param RefundRequest $request Refund request parameters
     * @return array<string, mixed> Response with refundId and state
     * @throws \Auw\PhonePe\Exceptions\ApiException On API errors
     * @throws \Auw\PhonePe\Exceptions\ValidationException On validation errors
     */
    public function create(RefundRequest $request): array;

    /**
     * Check refund status
     *
     * @param string $merchantRefundId Merchant's refund ID
     * @return RefundStatusResponse Refund status response
     * @throws \Auw\PhonePe\Exceptions\ApiException On API errors
     */
    public function getStatus(string $merchantRefundId): RefundStatusResponse;
}
