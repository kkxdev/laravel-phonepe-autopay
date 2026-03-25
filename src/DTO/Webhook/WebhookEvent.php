<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\DTO\Webhook;

/**
 * Webhook Event DTO
 *
 * Represents a PhonePe webhook callback. The `event` field is the canonical
 * event identifier per the PhonePe docs; the deprecated `type` field is kept
 * as a fallback for older integrations.
 *
 * Authorization header validation: SHA256(username:password)
 * Timestamps (expireAt, timestamp) are epoch milliseconds.
 */
final class WebhookEvent
{
    public function __construct(
        /** Canonical event identifier (e.g. subscription.setup.order.completed) */
        public string $type,
        public array $payload
    ) {}

    /**
     * Parse a raw webhook body array into a WebhookEvent.
     *
     * Prefers the `event` field (current) over the deprecated `type` field.
     * Payload data is taken from the nested `payload` key when present.
     */
    public static function fromPayload(array $data): self
    {
        return new self(
            // `event` is the current field per PhonePe docs; `type` is deprecated
            type: $data['event'] ?? $data['type'] ?? '',
            payload: $data['payload'] ?? $data
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'payload' => $this->payload,
        ];
    }

    // -------------------------------------------------------------------------
    // Setup events
    // -------------------------------------------------------------------------

    public function isSubscriptionSetupCompleted(): bool
    {
        return $this->type === WebhookEventType::SUBSCRIPTION_SETUP_ORDER_COMPLETED;
    }

    public function isSubscriptionSetupFailed(): bool
    {
        return $this->type === WebhookEventType::SUBSCRIPTION_SETUP_ORDER_FAILED;
    }

    // -------------------------------------------------------------------------
    // Subscription state-change events
    // -------------------------------------------------------------------------

    public function isSubscriptionPaused(): bool
    {
        return $this->type === WebhookEventType::SUBSCRIPTION_PAUSED;
    }

    public function isSubscriptionUnpaused(): bool
    {
        return $this->type === WebhookEventType::SUBSCRIPTION_UNPAUSED;
    }

    public function isSubscriptionRevoked(): bool
    {
        return $this->type === WebhookEventType::SUBSCRIPTION_REVOKED;
    }

    public function isSubscriptionCancelled(): bool
    {
        return $this->type === WebhookEventType::SUBSCRIPTION_CANCELLED;
    }

    /** @deprecated Use isSubscriptionSetupCompleted() for setup or isSubscriptionRevoked()/isCancelled() for terminal states */
    public function isSubscriptionCompleted(): bool
    {
        return $this->type === WebhookEventType::SUBSCRIPTION_COMPLETED;
    }

    // -------------------------------------------------------------------------
    // Notification events
    // -------------------------------------------------------------------------

    public function isNotificationCompleted(): bool
    {
        return $this->type === WebhookEventType::SUBSCRIPTION_NOTIFICATION_COMPLETED;
    }

    public function isNotificationFailed(): bool
    {
        return $this->type === WebhookEventType::SUBSCRIPTION_NOTIFICATION_FAILED;
    }

    // -------------------------------------------------------------------------
    // Redemption events
    // -------------------------------------------------------------------------

    public function isRedemptionOrderCompleted(): bool
    {
        return $this->type === WebhookEventType::SUBSCRIPTION_REDEMPTION_ORDER_COMPLETED;
    }

    public function isRedemptionOrderFailed(): bool
    {
        return $this->type === WebhookEventType::SUBSCRIPTION_REDEMPTION_ORDER_FAILED;
    }

    public function isRedemptionTransactionCompleted(): bool
    {
        return $this->type === WebhookEventType::SUBSCRIPTION_REDEMPTION_TRANSACTION_COMPLETED;
    }

    public function isRedemptionTransactionFailed(): bool
    {
        return $this->type === WebhookEventType::SUBSCRIPTION_REDEMPTION_TRANSACTION_FAILED;
    }

    /** Convenience: true for either redemption order or transaction completed event */
    public function isRedemptionCompleted(): bool
    {
        return $this->isRedemptionOrderCompleted() || $this->isRedemptionTransactionCompleted();
    }

    /** Convenience: true for either redemption order or transaction failed event */
    public function isRedemptionFailed(): bool
    {
        return $this->isRedemptionOrderFailed() || $this->isRedemptionTransactionFailed();
    }

    // -------------------------------------------------------------------------
    // Refund events
    // -------------------------------------------------------------------------

    public function isRefundAccepted(): bool
    {
        return $this->type === WebhookEventType::PG_REFUND_ACCEPTED;
    }

    public function isRefundCompleted(): bool
    {
        return $this->type === WebhookEventType::PG_REFUND_COMPLETED;
    }

    public function isRefundFailed(): bool
    {
        return $this->type === WebhookEventType::PG_REFUND_FAILED;
    }

    // -------------------------------------------------------------------------
    // Payload accessors
    // -------------------------------------------------------------------------

    /**
     * Root-level state — use this as the authoritative status field.
     * Possible values: COMPLETED, FAILED, PENDING
     */
    public function getState(): ?string
    {
        return $this->payload['state'] ?? null;
    }

    public function getMerchantId(): ?string
    {
        return $this->payload['merchantId'] ?? null;
    }

    public function getMerchantOrderId(): ?string
    {
        return $this->payload['merchantOrderId'] ?? null;
    }

    public function getOrderId(): ?string
    {
        return $this->payload['orderId'] ?? null;
    }

    /** Amount in Paise */
    public function getAmount(): ?int
    {
        return isset($this->payload['amount']) ? (int) $this->payload['amount'] : null;
    }

    /**
     * Epoch timestamp in milliseconds.
     * Use intdiv($this->getExpireAt(), 1000) to get seconds.
     */
    public function getExpireAt(): ?int
    {
        return isset($this->payload['expireAt']) ? (int) $this->payload['expireAt'] : null;
    }

    /**
     * Epoch timestamp in milliseconds.
     * Use intdiv($this->getTimestamp(), 1000) to get seconds.
     */
    public function getTimestamp(): ?int
    {
        return isset($this->payload['timestamp']) ? (int) $this->payload['timestamp'] : null;
    }

    /** Error code — only present on failed events */
    public function getErrorCode(): ?string
    {
        return $this->payload['errorCode'] ?? null;
    }

    /** Detailed error code — only present on failed events */
    public function getDetailedErrorCode(): ?string
    {
        return $this->payload['detailedErrorCode'] ?? null;
    }

    public function getMerchantSubscriptionId(): ?string
    {
        return $this->payload['merchantSubscriptionId'] ?? null;
    }

    public function getSubscriptionId(): ?string
    {
        return $this->payload['subscriptionId'] ?? null;
    }
}
