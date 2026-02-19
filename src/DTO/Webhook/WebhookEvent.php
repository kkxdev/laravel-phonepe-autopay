<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\DTO\Webhook;

/**
 * Webhook Event DTO
 */
final class WebhookEvent
{
    public function __construct(
        public string $type,
        public array $payload
    ) {}

    public static function fromPayload(array $data): self
    {
        return new self(
            type: $data['type'] ?? $data['event'] ?? '',
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

    public function isSubscriptionPaused(): bool
    {
        return $this->type === 'SUBSCRIPTION_PAUSED' || $this->type === 'subscription.paused';
    }

    public function isSubscriptionUnpaused(): bool
    {
        return $this->type === 'SUBSCRIPTION_UNPAUSED' || $this->type === 'subscription.unpaused';
    }

    public function isSubscriptionRevoked(): bool
    {
        return $this->type === 'SUBSCRIPTION_REVOKED' || $this->type === 'subscription.revoked';
    }

    public function isSubscriptionCompleted(): bool
    {
        return $this->type === 'SUBSCRIPTION_COMPLETED' || $this->type === 'subscription.completed';
    }

    public function isRedemptionCompleted(): bool
    {
        return $this->type === 'REDEMPTION_COMPLETED' || $this->type === 'redemption.completed';
    }

    public function isRedemptionFailed(): bool
    {
        return $this->type === 'REDEMPTION_FAILED' || $this->type === 'redemption.failed';
    }

    public function getState(): ?string
    {
        return $this->payload['state'] ?? null;
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
