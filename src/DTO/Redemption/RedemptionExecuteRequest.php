<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\DTO\Redemption;

use Kkxdev\PhonePe\Exceptions\ValidationException;

/**
 * Redemption Execute Request DTO
 */
final class RedemptionExecuteRequest
{
    public function __construct(
        public string $merchantOrderId,
        public ?string $idempotencyKey = null
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            merchantOrderId: $data['merchantOrderId'] ?? ''
        );
    }

    public function toArray(): array
    {
        $payload = [
            'merchantOrderId' => $this->merchantOrderId,
        ];

        if ($this->idempotencyKey !== null) {
            $payload['idempotencyKey'] = $this->idempotencyKey;
        }

        return $payload;
    }

    private function validate(): void
    {
        if (empty($this->merchantOrderId)) {
            throw ValidationException::missingField('merchantOrderId');
        }

        if (strlen($this->merchantOrderId) > 63) {
            throw ValidationException::invalidField(
                'merchantOrderId',
                'Must not exceed 63 characters'
            );
        }
    }
}
