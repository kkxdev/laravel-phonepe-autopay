<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Tests;

use Kkxdev\PhonePe\Providers\PhonePeServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Base Test Case
 */
abstract class TestCase extends Orchestra
{
    /**
     * Setup the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Set up test configuration
        config()->set('phonepe.environment', 'sandbox');
        config()->set('phonepe.credentials', [
            'merchant_id' => 'TEST_MERCHANT',
            'client_id' => 'TEST_CLIENT_ID',
            'client_secret' => 'TEST_CLIENT_SECRET',
            'client_version' => 'v1',
        ]);
        config()->set('phonepe.webhook', [
            'username' => 'test_user',
            'password' => 'test_pass',
        ]);
    }

    /**
     * Get package providers
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            PhonePeServiceProvider::class,
        ];
    }

    /**
     * Define environment setup
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        // Configure cache for tests
        config()->set('cache.default', 'array');
    }
}
