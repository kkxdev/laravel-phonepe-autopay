<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Contracts;

use Kkxdev\PhonePe\DTO\Redemption\RedemptionNotifyRequest;
use Kkxdev\PhonePe\DTO\Redemption\RedemptionExecuteRequest;
use Kkxdev\PhonePe\DTO\Redemption\RedemptionStatusResponse;

/**
 * Redemption API Interface
 *
 * Handles recurring payment redemptions (notify, execute, status).
 */
interface RedemptionApiInterface
{
    /**
     * Notify PhonePe about upcoming redemption
     *
     * Must be called 24 hours before executing redemption.
     *
     * @param RedemptionNotifyRequest $request Notify request parameters
     * @return array<string, mixed> Response with orderId, state, expireAt
     * @throws \KKxdev\PhonePe\Exceptions\ApiException On API errors
     * @throws \KKxdev\PhonePe\Exceptions\ValidationException On validation errors
     */
    public function notify(RedemptionNotifyRequest $request): array;

    /**
     * Execute redemption (charge customer)
     *
     * Must be called after 24-hour notification period.
     *
     * @param RedemptionExecuteRequest $request Execute request parameters
     * @return array<string, mixed> Response with state (COMPLETED/FAILED/PENDING) and transactionId
     * @throws \KKxdev\PhonePe\Exceptions\ApiException On API errors
     * @throws \KKxdev\PhonePe\Exceptions\ValidationException On validation errors
     */
    public function execute(RedemptionExecuteRequest $request): array;

    /**
     * Check redemption order status
     *
     * @param string $merchantOrderId Merchant's redemption order ID
     * @param bool $includeDetails Include transaction details
     * @return RedemptionStatusResponse Redemption status response
     * @throws \KKxdev\PhonePe\Exceptions\ApiException On API errors
     */
    public function getStatus(string $merchantOrderId, bool $includeDetails = true): RedemptionStatusResponse;
}
