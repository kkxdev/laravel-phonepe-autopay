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
        public ?array $metaInfo = null
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            orderId: $data['orderId'] ?? '',
            merchantOrderId: $data['merchantOrderId'] ?? '',
            state: $data['state'] ?? '',
            amount: (int) ($data['amount'] ?? 0),
            paymentDetails: $data['paymentDetails'] ?? null,
            metaInfo: $data['metaInfo'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'orderId' => $this->orderId,
            'merchantOrderId' => $this->merchantOrderId,
            'state' => $this->state,
            'amount' => $this->amount,
            'paymentDetails' => $this->paymentDetails,
            'metaInfo' => $this->metaInfo,
        ];
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
}
