<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\DTO\Subscription;

use Kkxdev\PhonePe\Exceptions\ValidationException;

/**
 * Subscription Setup Request DTO
 */
final class SubscriptionSetupRequest
{
    public function __construct(
        public string $merchantOrderId,
        public int $amount,
        public string $merchantSubscriptionId,
        public string $subscriptionType,
        public string $authWorkflowType,
        public string $amountType,
        public int $maxAmount,
        public string $frequency,
        public string $productType,
        public string $redirectUrl,
        public string $cancelRedirectUrl,
        public ?string $message = null,
        public ?array $metaInfo = null,
        public ?string $type = null
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            merchantOrderId: $data['merchantOrderId'] ?? '',
            amount: (int) ($data['amount'] ?? 0),
            merchantSubscriptionId: $data['merchantSubscriptionId'],
            subscriptionType: $data['subscriptionType'],
            authWorkflowType: $data['authWorkflowType'],
            amountType: $data['amountType'],
            maxAmount: (int) ($data['maxAmount'] ?? 0),
            frequency: $data['frequency'],
            productType: $data['productType'],
            redirectUrl: $data['redirectUrl'],
            cancelRedirectUrl: $data['cancelRedirectUrl'],
            message: $data['message'],
            metaInfo: $data['metaInfo'] ?? null,
            type: $data['type'] ?? null
        );
    }

    public function toArray(): array
    {
        $payload = [
            'merchantOrderId' => $this->merchantOrderId,
            'amount' => $this->amount,
            'paymentFlow' => [
                'type' => $this->type ?? 'SUBSCRIPTION_CHECKOUT_SETUP',
                'merchantUrls' => [
                    'redirectUrl' => $this->redirectUrl,
                    'cancelRedirectUrl' => $this->cancelRedirectUrl,
                ],
                'subscriptionDetails' => [
                    'subscriptionType' => $this->subscriptionType,
                    'merchantSubscriptionId' => $this->merchantSubscriptionId,
                    'authWorkflowType' => $this->authWorkflowType,
                    'amountType' => $this->amountType,
                    'maxAmount' => $this->maxAmount,
                    'frequency' => $this->frequency,
                    'productType' => $this->productType,
                ],
            ],
        ];

        if ($this->message !== null) {
            $payload['paymentFlow']['message'] = $this->message;
        }

        if ($this->metaInfo !== null) {
            $payload['metaInfo'] = $this->metaInfo;
        }

        return $payload;
    }

    private function validate(): void
    {
        $errors = [];

        if (empty($this->merchantOrderId)) {
            $errors['merchantOrderId'] = ['Merchant order ID is required'];
        } elseif (strlen($this->merchantOrderId) > 63) {
            $errors['merchantOrderId'] = ['Merchant order ID must not exceed 63 characters'];
        }

        if ($this->amount < 100) {
            $errors['amount'] = ['Amount must be at least 100 paisa'];
        }

        if (empty($this->merchantSubscriptionId)) {
            $errors['merchantSubscriptionId'] = ['Merchant subscription ID is required'];
        }

        if ($this->maxAmount > 150000000) {
            $errors['maxAmount'] = ['Max amount cannot exceed 1,500,000 paisa (15,00,000 rupees)'];
        }

        $validFrequencies = ['DAILY', 'WEEKLY', 'FORTNIGHTLY', 'MONTHLY', 'QUARTERLY', 'HALFYEARLY', 'YEARLY'];
        if (!in_array($this->frequency, $validFrequencies, true)) {
            $errors['frequency'] = ['Frequency must be one of: ' . implode(', ', $validFrequencies)];
        }

        if (empty($this->redirectUrl)) {
            $errors['redirectUrl'] = ['Redirect URL is required'];
        }

        if (empty($this->cancelRedirectUrl)) {
            $errors['cancelRedirectUrl'] = ['Cancel redirect URL is required'];
        }

        if (!empty($errors)) {
            throw ValidationException::fromErrors($errors);
        }
    }
}
