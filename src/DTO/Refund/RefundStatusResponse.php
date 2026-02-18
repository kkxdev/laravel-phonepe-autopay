<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\DTO\Refund;

/**
 * Refund Status Response DTO
 */
final readonly class RefundStatusResponse
{
    public function __construct(
        public string $merchantRefundId,
        public string $refundId,
        public string $state,
        public int $amount,
        public ?int $timestamp = null,
        public ?string $errorCode = null
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            merchantRefundId: $data['merchantRefundId'] ?? '',
            refundId: $data['refundId'] ?? '',
            state: $data['state'] ?? '',
            amount: (int) ($data['amount'] ?? 0),
            timestamp: isset($data['timestamp']) ? (int) $data['timestamp'] : null,
            errorCode: $data['errorCode'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'merchantRefundId' => $this->merchantRefundId,
            'refundId' => $this->refundId,
            'state' => $this->state,
            'amount' => $this->amount,
            'timestamp' => $this->timestamp,
            'errorCode' => $this->errorCode,
        ], fn($v) => $v !== null);
    }

    public function isCompleted(): bool
    {
        return $this->state === 'COMPLETED';
    }

    public function isFailed(): bool
    {
        return $this->state === 'FAILED';
    }

    public function isPending(): bool
    {
        return $this->state === 'PENDING';
    }

    public function isConfirmed(): bool
    {
        return $this->state === 'CONFIRMED';
    }
}
