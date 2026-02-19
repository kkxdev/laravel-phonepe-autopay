<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\DTO\Refund;

use Kkxdev\PhonePe\Exceptions\ValidationException;

/**
 * Refund Request DTO
 */
final class RefundRequest
{
    public function __construct(
        public string $merchantRefundId,
        public string $originalMerchantOrderId,
        public int $amount
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            merchantRefundId: $data['merchantRefundId'] ?? '',
            originalMerchantOrderId: $data['originalMerchantOrderId'] ?? '',
            amount: (int) ($data['amount'] ?? 0)
        );
    }

    public function toArray(): array
    {
        return [
            'merchantRefundId' => $this->merchantRefundId,
            'originalMerchantOrderId' => $this->originalMerchantOrderId,
            'amount' => $this->amount,
        ];
    }

    private function validate(): void
    {
        $errors = [];

        if (empty($this->merchantRefundId)) {
            $errors['merchantRefundId'] = ['Merchant refund ID is required'];
        } elseif (strlen($this->merchantRefundId) > 63) {
            $errors['merchantRefundId'] = ['Merchant refund ID must not exceed 63 characters'];
        }

        if (empty($this->originalMerchantOrderId)) {
            $errors['originalMerchantOrderId'] = ['Original merchant order ID is required'];
        }

        if ($this->amount <= 0) {
            $errors['amount'] = ['Amount must be greater than 0'];
        }

        if (!empty($errors)) {
            throw ValidationException::fromErrors($errors);
        }
    }
}
