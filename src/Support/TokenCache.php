<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Support;

use Kkxdev\PhonePe\DTO\Auth\AuthTokenResponse;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * OAuth Token Cache Manager
 *
 * Handles caching of PhonePe OAuth tokens with TTL management.
 */
final class TokenCache
{
    public function __construct(
        private readonly CacheRepository $cache,
        private readonly string $cacheKeyPrefix,
        private readonly int $ttlBuffer
    ) {}

    /**
     * Store token in cache
     *
     * @param AuthTokenResponse $token
     * @return void
     */
    public function store(AuthTokenResponse $token): void
    {
        $ttl = $token->getTimeToExpiry() - $this->ttlBuffer;

        if ($ttl > 0) {
            $this->cache->put(
                $this->getCacheKey(),
                $token->toArray(),
                $ttl
            );
        }
    }

    /**
     * Get cached token
     *
     * @return AuthTokenResponse|null
     */
    public function get(): ?AuthTokenResponse
    {
        $data = $this->cache->get($this->getCacheKey());

        if ($data === null) {
            return null;
        }

        try {
            $token = AuthTokenResponse::fromResponse($data);

            // Double-check expiry
            if ($token->isExpired($this->ttlBuffer)) {
                $this->clear();
                return null;
            }

            return $token;
        } catch (\Throwable) {
            $this->clear();
            return null;
        }
    }

    /**
     * Get formatted authorization header
     *
     * @return string|null
     */
    public function getAuthorizationHeader(): ?string
    {
        $token = $this->get();
        return $token?->getAuthorizationHeader();
    }

    /**
     * Check if valid token exists
     *
     * @return bool
     */
    public function has(): bool
    {
        return $this->get() !== null;
    }

    /**
     * Clear cached token
     *
     * @return void
     */
    public function clear(): void
    {
        $this->cache->forget($this->getCacheKey());
    }

    /**
     * Get cache key
     *
     * @return string
     */
    private function getCacheKey(): string
    {
        return $this->cacheKeyPrefix;
    }
}
