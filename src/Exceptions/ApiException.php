<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Exceptions;

/**
 * API Exception
 *
 * Thrown when PhonePe API returns an error response (4xx, 5xx).
 */
class ApiException extends PhonePeException
{
    protected int $statusCode;
    protected array $responseBody;

    public function __construct(
        string $message,
        int $statusCode,
        array $responseBody = [],
        ?\Throwable $previous = null
    ) {
        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;

        $context = [
            'status_code' => $statusCode,
            'response_body' => $responseBody,
        ];

        parent::__construct($message, $statusCode, $previous, $context);
    }

    /**
     * Get HTTP status code
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get response body
     *
     * @return array<string, mixed>
     */
    public function getResponseBody(): array
    {
        return $this->responseBody;
    }

    /**
     * Create from HTTP response
     *
     * @param int $statusCode
     * @param array<string, mixed> $responseBody
     * @return static
     */
    public static function fromResponse(int $statusCode, array $responseBody): static
    {
        $message = $responseBody['message'] ?? $responseBody['error'] ?? 'PhonePe API error';

        return new static($message, $statusCode, $responseBody);
    }

    /**
     * Check if error is server-side (5xx)
     *
     * @return bool
     */
    public function isServerError(): bool
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * Check if error is client-side (4xx)
     *
     * @return bool
     */
    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }
}
