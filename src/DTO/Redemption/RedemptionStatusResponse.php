<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\DTO\Redemption;

/**
 * Redemption Status Response DTO
 */
final class RedemptionStatusResponse
{
    public function __construct(
        public string $merchantOrderId,
        public string $orderId,
        public string $state,
        public int $amount,
        public ?string $transactionId = null,
        public ?array $transactionDetails = null,
        public ?string $errorCode = null
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            merchantOrderId: $data['merchantOrderId'] ?? '',
            orderId: $data['orderId'] ?? '',
            state: $data['state'] ?? '',
            amount: (int) ($data['amount'] ?? 0),
            transactionId: $data['transactionId'] ?? null,
            transactionDetails: $data['transactionDetails'] ?? null,
            errorCode: $data['errorCode'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'merchantOrderId' => $this->merchantOrderId,
            'orderId' => $this->orderId,
            'state' => $this->state,
            'amount' => $this->amount,
            'transactionId' => $this->transactionId,
            'transactionDetails' => $this->transactionDetails,
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

    public function isNotified(): bool
    {
        return $this->state === 'NOTIFIED';
    }
}
