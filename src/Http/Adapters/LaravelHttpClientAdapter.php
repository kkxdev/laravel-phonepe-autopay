<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Http\Adapters;

use Kkxdev\PhonePe\Contracts\HttpClientInterface;
use Kkxdev\PhonePe\Exceptions\ApiException;
use Kkxdev\PhonePe\Exceptions\NetworkException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/**
 * Laravel HTTP Client Adapter
 *
 * Adapts Laravel's HTTP client to HttpClientInterface.
 */
final class LaravelHttpClientAdapter implements HttpClientInterface
{
    private int $connectTimeout = 5;
    private int $timeout = 15;

    /**
     * {@inheritDoc}
     */
    public function send(
        string $method,
        string $url,
        array $headers = [],
        ?array $body = null,
        string $contentType = 'json'
    ): array {
        try {
            $request = Http::timeout($this->timeout)
                ->withHeaders($headers);

            // connectTimeout is only available in Laravel 9+
            if (method_exists($request, 'connectTimeout')) {
                $request = $request->connectTimeout($this->connectTimeout);
            }

            if ($contentType === 'form') {
                $request = $request->asForm();
            }

            // Handle requests with no body (null) separately
            // This is important for APIs like PhonePe cancel that expect no body
            if ($body === null) {
                $response = $request->send(strtoupper($method), $url);
            } else {
                $response = match (strtoupper($method)) {
                    'GET' => $request->get($url, $body),
                    'POST' => $request->post($url, $body),
                    'PUT' => $request->put($url, $body),
                    'PATCH' => $request->patch($url, $body),
                    'DELETE' => $request->delete($url, $body),
                    default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
                };
            }

            if (!$response->successful()) {
                throw ApiException::fromResponse(
                    $response->status(),
                    $response->json() ?? ['error' => $response->body()]
                );
            }

            // Handle 204 No Content responses (e.g., subscription cancel)
            if ($response->status() === 204) {
                return [];
            }

            return $response->json() ?? [];

        } catch (ConnectionException $e) {
            throw NetworkException::connectionFailed($url, $e->getMessage());
        } catch (RequestException $e) {
            if ($e->response) {
                throw ApiException::fromResponse(
                    $e->response->status(),
                    $e->response->json() ?? ['error' => $e->response->body()]
                );
            }
            throw NetworkException::requestTimeout($url, $this->timeout);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function withConnectTimeout(int $seconds): self
    {
        $clone = clone $this;
        $clone->connectTimeout = $seconds;
        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withTimeout(int $seconds): self
    {
        $clone = clone $this;
        $clone->timeout = $seconds;
        return $clone;
    }
}
