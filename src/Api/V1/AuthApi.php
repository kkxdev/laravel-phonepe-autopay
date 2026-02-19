<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Api\V1;

use Kkxdev\PhonePe\Contracts\AuthApiInterface;
use Kkxdev\PhonePe\Contracts\HttpClientInterface;
use Kkxdev\PhonePe\DTO\Auth\AuthTokenRequest;
use Kkxdev\PhonePe\DTO\Auth\AuthTokenResponse;
use Kkxdev\PhonePe\Exceptions\AuthenticationException;
use Kkxdev\PhonePe\Support\EndpointBuilder;
use Kkxdev\PhonePe\Support\TokenCache;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Authentication API v1
 */
final class AuthApi implements AuthApiInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private EndpointBuilder $endpointBuilder,
        private TokenCache $tokenCache,
        private AuthTokenRequest $credentials,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * {@inheritDoc}
     */
    public function fetchToken(AuthTokenRequest $request): AuthTokenResponse
    {
        try {
            $url = $this->endpointBuilder->buildUrl($this->endpointBuilder->authToken());

            $this->logger->info('Fetching OAuth token', ['url' => $url]);

            $response = $this->httpClient->send(
                'POST',
                $url,
                [],
                $request->toArray(),
                'form'
            );

            $token = AuthTokenResponse::fromResponse($response);

            $this->logger->info('OAuth token fetched successfully', [
                'expires_at' => $token->expiresAt,
                'time_to_expiry' => $token->getTimeToExpiry(),
            ]);

            return $token;

        } catch (\Throwable $e) {
            $this->logger->error('Failed to fetch OAuth token', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            throw AuthenticationException::tokenFetchFailed($e->getMessage(), [
                'exception' => get_class($e),
            ]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getToken(): string
    {
        // Try to get from cache
        $cachedToken = $this->tokenCache->get();

        if ($cachedToken !== null) {
            $this->logger->debug('Using cached OAuth token');
            return $cachedToken->accessToken;
        }

        // Fetch new token
        $this->logger->info('Cache miss or expired token, fetching new token');
        $token = $this->fetchToken($this->credentials);

        // Cache it
        $this->tokenCache->store($token);

        return $token->accessToken;
    }

    /**
     * {@inheritDoc}
     */
    public function clearToken(): void
    {
        $this->logger->info('Clearing cached OAuth token');
        $this->tokenCache->clear();
    }

    /**
     * Get authorization header value
     *
     * @return string
     */
    public function getAuthorizationHeader(): string
    {
        $cachedHeader = $this->tokenCache->getAuthorizationHeader();

        if ($cachedHeader !== null) {
            return $cachedHeader;
        }

        $token = $this->getToken();
        return "O-Bearer {$token}";
    }
}
