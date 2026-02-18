<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Tests\Feature;

use Kkxdev\PhonePe\Contracts\HttpClientInterface;
use Kkxdev\PhonePe\DTO\Subscription\SubscriptionSetupRequest;
use Kkxdev\PhonePe\Facades\PhonePe;
use Kkxdev\PhonePe\Tests\TestCase;
use Mockery;

/**
 * Subscription API Integration Tests
 */
class SubscriptionApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mock HTTP client
        $this->mockHttpClient();
    }

    /** @test */
    public function it_can_setup_subscription(): void
    {
        $request = SubscriptionSetupRequest::fromArray([
            'merchantOrderId' => 'ORDER_123',
            'amount' => 100000,
            'merchantSubscriptionId' => 'SUB_123',
            'subscriptionType' => 'RECURRING',
            'authWorkflowType' => 'TRANSACTION',
            'amountType' => 'FIXED',
            'maxAmount' => 100000,
            'frequency' => 'MONTHLY',
            'productType' => 'UPI_MANDATE',
            'redirectUrl' => 'https://example.com/success',
            'cancelRedirectUrl' => 'https://example.com/cancel',
        ]);

        $response = PhonePe::subscription()->setup($request);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('orderId', $response);
        $this->assertArrayHasKey('state', $response);
        $this->assertEquals('PENDING', $response['state']);
    }

    /** @test */
    public function it_can_get_order_status(): void
    {
        $status = PhonePe::subscription()->getOrderStatus('ORDER_123');

        $this->assertEquals('ORDER_123', $status->merchantOrderId);
        $this->assertEquals('COMPLETED', $status->state);
        $this->assertTrue($status->isCompleted());
    }

    /** @test */
    public function it_can_get_subscription_status(): void
    {
        $status = PhonePe::subscription()->getStatus('SUB_123');

        $this->assertEquals('SUB_123', $status->merchantSubscriptionId);
        $this->assertEquals('ACTIVE', $status->state);
        $this->assertTrue($status->isActive());
    }

    /** @test */
    public function it_can_cancel_subscription(): void
    {
        $this->expectNotToPerformAssertions();

        PhonePe::subscription()->cancel('SUB_123');
    }

    /**
     * Mock HTTP client responses
     */
    private function mockHttpClient(): void
    {
        $mock = Mockery::mock(HttpClientInterface::class);

        // Mock OAuth token response
        $mock->shouldReceive('send')
            ->with('POST', Mockery::type('string'), [], Mockery::type('array'), 'form')
            ->andReturn([
                'access_token' => 'test_token',
                'token_type' => 'O-Bearer',
                'expires_at' => time() + 3600,
            ]);

        // Mock subscription setup
        $mock->shouldReceive('send')
            ->with('POST', Mockery::pattern('/\/checkout\/v2\/pay$/'), Mockery::type('array'), Mockery::type('array'))
            ->andReturn([
                'orderId' => 'PG_ORDER_123',
                'state' => 'PENDING',
                'redirectUrl' => 'https://phonepe.com/...',
                'expireAt' => time() + 900,
            ]);

        // Mock order status
        $mock->shouldReceive('send')
            ->with('GET', Mockery::pattern('/\/order\/ORDER_123\/status/'), Mockery::type('array'))
            ->andReturn([
                'orderId' => 'PG_ORDER_123',
                'merchantOrderId' => 'ORDER_123',
                'state' => 'COMPLETED',
                'amount' => 100000,
                'paymentDetails' => [
                    ['transactionId' => 'TXN_123'],
                ],
            ]);

        // Mock subscription status
        $mock->shouldReceive('send')
            ->with('GET', Mockery::pattern('/\/subscriptions\/v2\/SUB_123\/status/'), Mockery::type('array'))
            ->andReturn([
                'merchantSubscriptionId' => 'SUB_123',
                'subscriptionId' => 'PG_SUB_123',
                'state' => 'ACTIVE',
                'authWorkflowType' => 'TRANSACTION',
                'amountType' => 'FIXED',
                'maxAmount' => 100000,
                'frequency' => 'MONTHLY',
            ]);

        // Mock subscription cancel
        $mock->shouldReceive('send')
            ->with('POST', Mockery::pattern('/\/subscriptions\/v2\/SUB_123\/cancel$/'), Mockery::type('array'), [])
            ->andReturn([]);

        $this->app->instance(HttpClientInterface::class, $mock);
    }
}
