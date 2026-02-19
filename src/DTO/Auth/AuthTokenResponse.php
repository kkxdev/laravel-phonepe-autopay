<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\DTO\Auth;

use Kkxdev\PhonePe\Exceptions\ValidationException;

/**
 * Auth Token Response DTO
 *
 * Immutable value object for OAuth token responses.
 */
final class AuthTokenResponse
{
    public function __construct(
        public string $accessToken,
        public string $tokenType,
        public int $expiresAt
    ) {
        $this->validate();
    }

    /**
     * Create from API response
     *
     * @param array<string, mixed> $data
     * @return self
     * @throws ValidationException
     */
    public static function fromResponse(array $data): self
    {
        return new self(
            accessToken: $data['access_token'] ?? '',
            tokenType: $data['token_type'] ?? 'O-Bearer',
            expiresAt: (int) ($data['expires_at'] ?? 0)
        );
    }

    /**
     * Convert to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'token_type' => $this->tokenType,
            'expires_at' => $this->expiresAt,
        ];
    }

    /**
     * Get formatted authorization header value
     *
     * @return string e.g., "O-Bearer abc123"
     */
    public function getAuthorizationHeader(): string
    {
        return "{$this->tokenType} {$this->accessToken}";
    }

    /**
     * Check if token is expired
     *
     * @param int $bufferSeconds Safety buffer before actual expiry (default 90s)
     * @return bool
     */
    public function isExpired(int $bufferSeconds = 90): bool
    {
        return time() >= ($this->expiresAt - $bufferSeconds);
    }

    /**
     * Get seconds until expiry
     *
     * @return int Seconds remaining (negative if expired)
     */
    public function getTimeToExpiry(): int
    {
        return $this->expiresAt - time();
    }

    /**
     * Validate DTO
     *
     * @throws ValidationException
     */
    private function validate(): void
    {
        $errors = [];

        if (empty($this->accessToken)) {
            $errors['access_token'] = ['Access token is required'];
        }

        if (empty($this->tokenType)) {
            $errors['token_type'] = ['Token type is required'];
        }

        if ($this->expiresAt <= 0) {
            $errors['expires_at'] = ['Expires at must be a valid timestamp'];
        }

        if (!empty($errors)) {
            throw ValidationException::fromErrors($errors);
        }
    }
}
