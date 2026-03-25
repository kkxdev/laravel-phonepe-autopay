<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\DTO\Subscription;

/**
 * Order Status Response DTO
 */
final class OrderStatusResponse
{
    public function __construct(
        public string $orderId,
        public string $merchantOrderId,
        public string $state,
        public int $amount,
        public ?array $paymentDetails = null,
        public ?array $metaInfo = null,
        /** Epoch milliseconds — convert with intdiv($expireAt, 1000) for seconds */
        public ?int $expireAt = null,
        /** Epoch milliseconds */
        public ?int $timestamp = null,
        /** Failure reason — only present when state is FAILED */
        public ?string $errorCode = null,
        /** Detailed failure reason — only present when state is FAILED */
        public ?string $detailedErrorCode = null
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            orderId: $data['orderId'] ?? '',
            merchantOrderId: $data['merchantOrderId'] ?? '',
            state: $data['state'] ?? '',
            amount: (int) ($data['amount'] ?? 0),
            paymentDetails: $data['paymentDetails'] ?? null,
            metaInfo: $data['metaInfo'] ?? null,
            expireAt: isset($data['expireAt']) ? (int) $data['expireAt'] : null,
            timestamp: isset($data['timestamp']) ? (int) $data['timestamp'] : null,
            errorCode: $data['errorCode'] ?? null,
            detailedErrorCode: $data['detailedErrorCode'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'orderId' => $this->orderId,
            'merchantOrderId' => $this->merchantOrderId,
            'state' => $this->state,
            'amount' => $this->amount,
            'paymentDetails' => $this->paymentDetails,
            'metaInfo' => $this->metaInfo,
            'expireAt' => $this->expireAt,
            'timestamp' => $this->timestamp,
            'errorCode' => $this->errorCode,
            'detailedErrorCode' => $this->detailedErrorCode,
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

    /**
     * Whether this order requires backend reconciliation.
     *
     * Per UAT checklist: PENDING orders must be polled on the following schedule:
     *   20-25 s → every 3 s (30 s) → every 6 s (60 s) → every 10 s (60 s) →
     *   every 30 s (60 s) → every 1 min until terminal state or expireAfter reached.
     */
    public function requiresReconciliation(): bool
    {
        return $this->isPending();
    }
}
