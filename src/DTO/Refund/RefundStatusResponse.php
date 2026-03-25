<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\DTO\Refund;

/**
 * Refund Status Response DTO
 */
final class RefundStatusResponse
{
    public function __construct(
        public string $merchantRefundId,
        public string $refundId,
        public string $state,
        public int $amount,
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
            merchantRefundId: $data['merchantRefundId'] ?? '',
            refundId: $data['refundId'] ?? '',
            state: $data['state'] ?? '',
            amount: (int) ($data['amount'] ?? 0),
            timestamp: isset($data['timestamp']) ? (int) $data['timestamp'] : null,
            errorCode: $data['errorCode'] ?? null,
            detailedErrorCode: $data['detailedErrorCode'] ?? null
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

    public function isConfirmed(): bool
    {
        return $this->state === 'CONFIRMED';
    }

    /**
     * Whether the refund is still in a non-terminal state and should be polled.
     *
     * Per UAT checklist: continue calling Refund Status API while PENDING or CONFIRMED.
     * Do NOT initiate a new refund while this returns true.
     */
    public function requiresPolling(): bool
    {
        return $this->isPending() || $this->isConfirmed();
    }

    /**
     * Whether a new refund must be initiated with a fresh merchantRefundId.
     *
     * Per UAT checklist: a FAILED refund cannot be retried with the same ID.
     */
    public function needsNewRefundId(): bool
    {
        return $this->isFailed();
    }
}
