<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Support;

/**
 * Environment Resolver
 *
 * Resolves environment-specific URLs (sandbox vs production).
 */
final class EnvironmentResolver
{
    private const SANDBOX_BASE_URL = 'https://api-preprod.phonepe.com/apis';
    private const PRODUCTION_BASE_URL = 'https://api.phonepe.com/apis';

    public function __construct(
        private string $environment,
        private array $urls
    ) {}

    /**
     * Get base URL for current environment
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->isSandbox()
            ? ($this->urls['sandbox']['base_url'] ?? self::SANDBOX_BASE_URL)
            : ($this->urls['production']['base_url'] ?? self::PRODUCTION_BASE_URL);
    }

    /**
     * Get success redirect URL
     *
     * @return string
     */
    public function getSuccessUrl(): string
    {
        $key = $this->isSandbox() ? 'sandbox' : 'production';
        return $this->urls[$key]['redirect_success'] ?? '';
    }

    /**
     * Get failure redirect URL
     *
     * @return string
     */
    public function getFailureUrl(): string
    {
        $key = $this->isSandbox() ? 'sandbox' : 'production';
        return $this->urls[$key]['redirect_failure'] ?? '';
    }

    /**
     * Check if running in sandbox
     *
     * @return bool
     */
    public function isSandbox(): bool
    {
        return strtolower($this->environment) === 'sandbox';
    }

    /**
     * Check if running in production
     *
     * @return bool
     */
    public function isProduction(): bool
    {
        return !$this->isSandbox();
    }

    /**
     * Get OAuth endpoint prefix
     *
     * @return string
     */
    public function getOAuthPrefix(): string
    {
        return $this->isSandbox() ? 'pg-sandbox' : 'identity-manager';
    }

    /**
     * Get payment gateway endpoint prefix
     *
     * @return string
     */
    public function getPgPrefix(): string
    {
        return $this->isSandbox() ? 'pg-sandbox' : 'pg';
    }
}
