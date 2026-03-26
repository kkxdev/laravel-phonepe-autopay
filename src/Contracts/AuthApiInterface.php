<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Contracts;

use Kkxdev\PhonePe\DTO\Auth\AuthTokenRequest;
use Kkxdev\PhonePe\DTO\Auth\AuthTokenResponse;

/**
 * Authentication API Interface
 *
 * Handles OAuth token lifecycle for PhonePe API authentication.
 */
interface AuthApiInterface
{
    /**
     * Fetch OAuth access token
     *
     * @param AuthTokenRequest $request Token request parameters
     * @return AuthTokenResponse Token response with access token and expiry
     * @throws \KKxdev\PhonePe\Exceptions\AuthenticationException On auth failures
     */
    public function fetchToken(AuthTokenRequest $request): AuthTokenResponse;

    /**
     * Get current valid access token (fetches new if expired)
     *
     * @return string Valid access token
     * @throws \KKxdev\PhonePe\Exceptions\AuthenticationException On auth failures
     */
    public function getToken(): string;

    /**
     * Clear cached token (force refresh on next request)
     *
     * @return void
     */
    public function clearToken(): void;
}
