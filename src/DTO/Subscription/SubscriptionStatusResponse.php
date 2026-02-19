<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\DTO\Subscription;

/**
 * Subscription Status Response DTO
 */
final class SubscriptionStatusResponse
{
    public function __construct(
        public string $merchantSubscriptionId,
        public string $subscriptionId,
        public string $state,
        public string $authWorkflowType,
        public string $amountType,
        public int $maxAmount,
        public string $frequency,
        public ?int $expiryTime = null,
        public ?int $pauseStartDate = null,
        public ?int $pauseEndDate = null
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            merchantSubscriptionId: $data['merchantSubscriptionId'] ?? '',
            subscriptionId: $data['subscriptionId'] ?? '',
            state: $data['state'] ?? '',
            authWorkflowType: $data['authWorkflowType'] ?? '',
            amountType: $data['amountType'] ?? '',
            maxAmount: (int) ($data['maxAmount'] ?? 0),
            frequency: $data['frequency'] ?? '',
            expiryTime: isset($data['expiryTime']) ? (int) $data['expiryTime'] : null,
            pauseStartDate: isset($data['pauseStartDate']) ? (int) $data['pauseStartDate'] : null,
            pauseEndDate: isset($data['pauseEndDate']) ? (int) $data['pauseEndDate'] : null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'merchantSubscriptionId' => $this->merchantSubscriptionId,
            'subscriptionId' => $this->subscriptionId,
            'state' => $this->state,
            'authWorkflowType' => $this->authWorkflowType,
            'amountType' => $this->amountType,
            'maxAmount' => $this->maxAmount,
            'frequency' => $this->frequency,
            'expiryTime' => $this->expiryTime,
            'pauseStartDate' => $this->pauseStartDate,
            'pauseEndDate' => $this->pauseEndDate,
        ], fn($v) => $v !== null);
    }

    public function isActive(): bool
    {
        return $this->state === 'ACTIVE';
    }

    public function isCancelled(): bool
    {
        return in_array($this->state, ['CANCELLED', 'REVOKED', 'EXPIRED', 'FAILED'], true);
    }

    public function isPaused(): bool
    {
        return $this->state === 'PAUSED';
    }
}
