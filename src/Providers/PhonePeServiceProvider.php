<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Providers;

use Kkxdev\PhonePe\Api\V1\AuthApi;
use Kkxdev\PhonePe\Api\V1\RedemptionApi;
use Kkxdev\PhonePe\Api\V1\RefundApi;
use Kkxdev\PhonePe\Api\V1\SubscriptionApi;
use Kkxdev\PhonePe\Contracts\AuthApiInterface;
use Kkxdev\PhonePe\Contracts\CircuitBreakerInterface;
use Kkxdev\PhonePe\Contracts\HttpClientInterface;
use Kkxdev\PhonePe\Contracts\RedemptionApiInterface;
use Kkxdev\PhonePe\Contracts\RefundApiInterface;
use Kkxdev\PhonePe\Contracts\RetryPolicyInterface;
use Kkxdev\PhonePe\Contracts\SubscriptionApiInterface;
use Kkxdev\PhonePe\Contracts\WebhookVerifierInterface;
use Kkxdev\PhonePe\DTO\Auth\AuthTokenRequest;
use Kkxdev\PhonePe\Http\Adapters\LaravelHttpClientAdapter;
use Kkxdev\PhonePe\PhonePeManager;
use Kkxdev\PhonePe\Resilience\CircuitBreaker\SimpleCircuitBreaker;
use Kkxdev\PhonePe\Resilience\Retry\ExponentialBackoffRetry;
use Kkxdev\PhonePe\Support\EndpointBuilder;
use Kkxdev\PhonePe\Support\EnvironmentResolver;
use Kkxdev\PhonePe\Support\TokenCache;
use Kkxdev\PhonePe\Support\WebhookVerifier;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

/**
 * PhonePe Service Provider
 */
class PhonePeServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/phonepe.php', 'phonepe');

        // Environment Resolver
        $this->app->singleton(EnvironmentResolver::class, function ($app) {
            return new EnvironmentResolver(
                environment: config('phonepe.environment', 'sandbox'),
                urls: config('phonepe.urls', [])
            );
        });

        // Endpoint Builder
        $this->app->singleton(EndpointBuilder::class, function ($app) {
            return new EndpointBuilder(
                $app->make(EnvironmentResolver::class)
            );
        });

        // Token Cache
        $this->app->singleton(TokenCache::class, function ($app) {
            $cacheStore = config('phonepe.token.cache_store');
            $cache = $cacheStore ? $app['cache']->store($cacheStore) : $app['cache']->store();

            return new TokenCache(
                cache: $cache,
                cacheKeyPrefix: config('phonepe.token.cache_key_prefix', 'phonepe_oauth_token'),
                ttlBuffer: config('phonepe.token.cache_ttl_buffer', 90)
            );
        });

        // HTTP Client
        $this->app->singleton(HttpClientInterface::class, function ($app) {
            $adapter = new LaravelHttpClientAdapter();

            $timeout = config('phonepe.resilience.timeout', []);
            if (isset($timeout['connect_seconds'])) {
                $adapter = $adapter->withConnectTimeout($timeout['connect_seconds']);
            }
            if (isset($timeout['request_seconds'])) {
                $adapter = $adapter->withTimeout($timeout['request_seconds']);
            }

            return $adapter;
        });

        // Retry Policy
        $this->app->singleton(RetryPolicyInterface::class, function ($app) {
            $config = config('phonepe.resilience.retry', []);

            if (!($config['enabled'] ?? true)) {
                return new class implements RetryPolicyInterface {
                    public function execute(callable $operation): mixed { return $operation(); }
                    public function shouldRetry(\Throwable $exception): bool { return false; }
                    public function getDelay(int $attempt): int { return 0; }
                    public function getMaxAttempts(): int { return 1; }
                };
            }

            return new ExponentialBackoffRetry(
                maxAttempts: $config['max_attempts'] ?? 3,
                baseDelayMs: $config['base_delay_ms'] ?? 1000,
                maxDelayMs: $config['max_delay_ms'] ?? 10000,
                jitter: $config['jitter'] ?? true,
                logger: $app->make(LoggerInterface::class)
            );
        });

        // Circuit Breaker
        $this->app->singleton(CircuitBreakerInterface::class, function ($app) {
            $config = config('phonepe.resilience.circuit_breaker', []);

            if (!($config['enabled'] ?? true)) {
                return new class implements CircuitBreakerInterface {
                    public function execute(callable $operation): mixed { return $operation(); }
                    public function isOpen(): bool { return false; }
                    public function isClosed(): bool { return true; }
                    public function isHalfOpen(): bool { return false; }
                    public function recordSuccess(): void {}
                    public function recordFailure(): void {}
                    public function reset(): void {}
                };
            }

            $cacheStore = config('phonepe.token.cache_store');
            $cache = $cacheStore ? $app['cache']->store($cacheStore) : $app['cache']->store();

            return new SimpleCircuitBreaker(
                cache: $cache,
                failureThreshold: $config['failure_threshold'] ?? 5,
                successThreshold: $config['success_threshold'] ?? 2,
                cooldownSeconds: $config['cooldown_seconds'] ?? 60,
                logger: $app->make(LoggerInterface::class)
            );
        });

        // Auth Credentials
        $this->app->singleton(AuthTokenRequest::class, function ($app) {
            return AuthTokenRequest::fromArray(config('phonepe.credentials', []));
        });

        // Auth API
        $this->app->singleton(AuthApiInterface::class, function ($app) {
            return new AuthApi(
                httpClient: $app->make(HttpClientInterface::class),
                endpointBuilder: $app->make(EndpointBuilder::class),
                tokenCache: $app->make(TokenCache::class),
                credentials: $app->make(AuthTokenRequest::class),
                logger: $app->make(LoggerInterface::class)
            );
        });

        // Subscription API
        $this->app->singleton(SubscriptionApiInterface::class, function ($app) {
            return new SubscriptionApi(
                httpClient: $app->make(HttpClientInterface::class),
                endpointBuilder: $app->make(EndpointBuilder::class),
                authApi: $app->make(AuthApi::class),
                logger: $app->make(LoggerInterface::class)
            );
        });

        // Redemption API
        $this->app->singleton(RedemptionApiInterface::class, function ($app) {
            return new RedemptionApi(
                httpClient: $app->make(HttpClientInterface::class),
                endpointBuilder: $app->make(EndpointBuilder::class),
                authApi: $app->make(AuthApi::class),
                logger: $app->make(LoggerInterface::class)
            );
        });

        // Refund API
        $this->app->singleton(RefundApiInterface::class, function ($app) {
            return new RefundApi(
                httpClient: $app->make(HttpClientInterface::class),
                endpointBuilder: $app->make(EndpointBuilder::class),
                authApi: $app->make(AuthApi::class),
                logger: $app->make(LoggerInterface::class)
            );
        });

        // Webhook Verifier
        $this->app->singleton(WebhookVerifierInterface::class, function ($app) {
            return new WebhookVerifier(
                username: config('phonepe.webhook.username', ''),
                password: config('phonepe.webhook.password', '')
            );
        });

        // PhonePe Manager
        $this->app->singleton(PhonePeManager::class, function ($app) {
            return new PhonePeManager(
                authApi: $app->make(AuthApiInterface::class),
                subscriptionApi: $app->make(SubscriptionApiInterface::class),
                redemptionApi: $app->make(RedemptionApiInterface::class),
                refundApi: $app->make(RefundApiInterface::class),
                webhookVerifier: $app->make(WebhookVerifierInterface::class),
                version: config('phonepe.api_version', 'v1')
            );
        });

        // Alias
        $this->app->alias(PhonePeManager::class, 'phonepe');
    }

    /**
     * Bootstrap services
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/phonepe.php' => config_path('phonepe.php'),
            ], 'phonepe-config');
        }
    }

    /**
     * Get the services provided by the provider
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            EnvironmentResolver::class,
            EndpointBuilder::class,
            TokenCache::class,
            HttpClientInterface::class,
            RetryPolicyInterface::class,
            CircuitBreakerInterface::class,
            AuthTokenRequest::class,
            AuthApiInterface::class,
            SubscriptionApiInterface::class,
            RedemptionApiInterface::class,
            RefundApiInterface::class,
            WebhookVerifierInterface::class,
            PhonePeManager::class,
            'phonepe',
        ];
    }
}
