<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\DTO\Auth;

use Kkxdev\PhonePe\Exceptions\ValidationException;

/**
 * Auth Token Request DTO
 *
 * Immutable value object for OAuth token requests.
 */
final readonly class AuthTokenRequest
{
    public function __construct(
        public string $clientId,
        public string $clientSecret,
        public string $clientVersion,
        public string $grantType = 'client_credentials'
    ) {
        $this->validate();
    }

    /**
     * Create from array
     *
     * @param array<string, mixed> $data
     * @return self
     * @throws ValidationException
     */
    public static function fromArray(array $data): self
    {
        return new self(
            clientId: $data['client_id'] ?? $data['clientId'] ?? '',
            clientSecret: $data['client_secret'] ?? $data['clientSecret'] ?? '',
            clientVersion: $data['client_version'] ?? $data['clientVersion'] ?? '',
            grantType: $data['grant_type'] ?? $data['grantType'] ?? 'client_credentials'
        );
    }

    /**
     * Convert to array for API request
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'client_version' => $this->clientVersion,
            'grant_type' => $this->grantType,
        ];
    }

    /**
     * Validate DTO
     *
     * @throws ValidationException
     */
    private function validate(): void
    {
        $errors = [];

        if (empty($this->clientId)) {
            $errors['client_id'] = ['Client ID is required'];
        }

        if (empty($this->clientSecret)) {
            $errors['client_secret'] = ['Client secret is required'];
        }

        if (empty($this->clientVersion)) {
            $errors['client_version'] = ['Client version is required'];
        }

        if ($this->grantType !== 'client_credentials') {
            $errors['grant_type'] = ['Grant type must be "client_credentials"'];
        }

        if (!empty($errors)) {
            throw ValidationException::fromErrors($errors);
        }
    }
}
