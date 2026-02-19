<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\DTO\Redemption;

use Kkxdev\PhonePe\Exceptions\ValidationException;

/**
 * Redemption Notify Request DTO
 */
final class RedemptionNotifyRequest
{
    public function __construct(
        public string $merchantOrderId,
        public int $amount,
        public string $merchantSubscriptionId,
        public string $redemptionRetryStrategy = 'STANDARD'
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            merchantOrderId: $data['merchantOrderId'] ?? '',
            amount: (int) ($data['amount'] ?? 0),
            merchantSubscriptionId: $data['merchantSubscriptionId'] ?? '',
            redemptionRetryStrategy: $data['redemptionRetryStrategy'] ?? 'STANDARD'
        );
    }

    public function toArray(): array
    {
        return [
            'merchantOrderId' => $this->merchantOrderId,
            'amount' => $this->amount,
            'paymentFlow' => [
                'type' => 'SUBSCRIPTION_REDEMPTION',
                'merchantSubscriptionId' => $this->merchantSubscriptionId,
            ],
            'redemptionRetryStrategy' => $this->redemptionRetryStrategy,
        ];
    }

    private function validate(): void
    {
        $errors = [];

        if (empty($this->merchantOrderId)) {
            $errors['merchantOrderId'] = ['Merchant order ID is required'];
        } elseif (strlen($this->merchantOrderId) > 63) {
            $errors['merchantOrderId'] = ['Merchant order ID must not exceed 63 characters'];
        }

        if ($this->amount <= 0) {
            $errors['amount'] = ['Amount must be greater than 0'];
        }

        if (empty($this->merchantSubscriptionId)) {
            $errors['merchantSubscriptionId'] = ['Merchant subscription ID is required'];
        }

        if (!in_array($this->redemptionRetryStrategy, ['STANDARD', 'CUSTOM'], true)) {
            $errors['redemptionRetryStrategy'] = ['Retry strategy must be STANDARD or CUSTOM'];
        }

        if (!empty($errors)) {
            throw ValidationException::fromErrors($errors);
        }
    }
}
