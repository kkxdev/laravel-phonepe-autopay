<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Contracts;

/**
 * HTTP Client Adapter Interface
 *
 * Provides abstraction over HTTP transport implementations.
 * Enables switching between Laravel HTTP, Guzzle, or other HTTP clients.
 */
interface HttpClientInterface
{
    /**
     * Send an HTTP request
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $url Full URL including query parameters
     * @param array<string, mixed> $headers Request headers
     * @param array<string, mixed>|null $body Request body (JSON serializable)
     * @param string $contentType Content type ('json' or 'form')
     * @return array<string, mixed> Parsed JSON response
     * @throws \Auw\PhonePe\Exceptions\NetworkException On network failures
     * @throws \Auw\PhonePe\Exceptions\ApiException On API errors (4xx, 5xx)
     */
    public function send(
        string $method,
        string $url,
        array $headers = [],
        ?array $body = null,
        string $contentType = 'json'
    ): array;

    /**
     * Set connection timeout in seconds
     *
     * @param int $seconds Timeout in seconds
     * @return self
     */
    public function withConnectTimeout(int $seconds): self;

    /**
     * Set request timeout in seconds
     *
     * @param int $seconds Timeout in seconds
     * @return self
     */
    public function withTimeout(int $seconds): self;
}
