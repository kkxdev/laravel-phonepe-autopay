<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Contracts;

use Kkxdev\PhonePe\DTO\Subscription\SubscriptionSetupRequest;
use Kkxdev\PhonePe\DTO\Subscription\OrderStatusResponse;
use Kkxdev\PhonePe\DTO\Subscription\SubscriptionStatusResponse;

/**
 * Subscription API Interface
 *
 * Manages subscription lifecycle: setup, status checks, and cancellation.
 */
interface SubscriptionApiInterface
{
    /**
     * Setup a new subscription with AutoPay
     *
     * @param SubscriptionSetupRequest $request Subscription setup parameters
     * @return array<string, mixed> Response with orderId, state, redirectUrl
     * @throws \Auw\PhonePe\Exceptions\ApiException On API errors
     * @throws \Auw\PhonePe\Exceptions\ValidationException On validation errors
     */
    public function setup(SubscriptionSetupRequest $request): array;

    /**
     * Check subscription order status
     *
     * @param string $merchantOrderId Merchant's order ID
     * @param bool $includeDetails Include payment attempt details
     * @return OrderStatusResponse Order status response
     * @throws \Auw\PhonePe\Exceptions\ApiException On API errors
     */
    public function getOrderStatus(string $merchantOrderId, bool $includeDetails = true): OrderStatusResponse;

    /**
     * Check subscription status
     *
     * @param string $merchantSubscriptionId Merchant's subscription ID
     * @return SubscriptionStatusResponse Subscription status response
     * @throws \Auw\PhonePe\Exceptions\ApiException On API errors
     */
    public function getStatus(string $merchantSubscriptionId): SubscriptionStatusResponse;

    /**
     * Cancel an active subscription
     *
     * @param string $merchantSubscriptionId Merchant's subscription ID
     * @return void Returns 204 No Content on success
     * @throws \Auw\PhonePe\Exceptions\ApiException On API errors
     */
    public function cancel(string $merchantSubscriptionId): void;
}
