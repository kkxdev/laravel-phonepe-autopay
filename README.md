# PhonePe Payment Gateway SDK for Laravel

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-8.x%20|%209.x%20|%2010.x%20|%2011.x%20|%2012.x-red.svg)](https://laravel.com)
[![Latest Version](https://img.shields.io/badge/version-1.0.0-green.svg)](https://github.com/kkxdev/laravel-phonepe-autopay/releases)

Production-grade PhonePe Payment Gateway SDK for Laravel with full **AutoPay (Recurring Payments)** support. Built with enterprise resilience patterns, comprehensive error handling, and complete test coverage.

> **Version 1.0.0** - Initial stable release with full PhonePe AutoPay API coverage

## ✨ Features

- ✅ **Complete API Coverage** - All PhonePe AutoPay endpoints implemented
- ✅ **OAuth Token Management** - Automatic token caching and refresh
- ✅ **Subscription Lifecycle** - Setup, status, pause, unpause, cancel, revoke
- ✅ **Recurring Redemption** - Notify, execute, and track recurring payments
- ✅ **Refund Management** - Create and track refunds
- ✅ **Webhook Verification** - SHA256 signature validation
- ✅ **Enterprise Resilience** - Retry policy with exponential backoff
- ✅ **Circuit Breaker** - Protection against cascading failures
- ✅ **Immutable DTOs** - Type-safe request/response objects with validation
- ✅ **PSR-3 Logging** - Comprehensive logging throughout
- ✅ **Laravel 8.x | 9.x | 10.x | 11.x | 12.x** - Compatible with modern Laravel versions
- ✅ **PHP 8.0+** - Modern PHP with strict types

## 📦 Installation

Install via Composer:

```bash
composer require kkxdev/laravel-phonepe-autopay
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=phonepe-config
```

## ⚙️ Configuration

Add your PhonePe credentials to `.env`:

```env
# Environment: sandbox or production
PHONEPE_ENV=sandbox

# Credentials (get from PhonePe Dashboard)
PHONEPE_MERCHANT_ID=your_merchant_id
PHONEPE_CLIENT_ID=your_client_id
PHONEPE_CLIENT_SECRET=your_client_secret
PHONEPE_CLIENT_VERSION=v1

# Redirect URLs
PHONEPE_SUCCESS_URL=https://yourdomain.com/payment/success
PHONEPE_FAILURE_URL=https://yourdomain.com/payment/failure

# Webhook Configuration
PHONEPE_WEBHOOK_USERNAME=your_webhook_username
PHONEPE_WEBHOOK_PASSWORD=your_webhook_password

# Optional: Resilience Settings
PHONEPE_RETRY_ENABLED=true
PHONEPE_RETRY_MAX_ATTEMPTS=3
PHONEPE_CIRCUIT_BREAKER_ENABLED=true
PHONEPE_CIRCUIT_BREAKER_THRESHOLD=5

# Optional: Logging
PHONEPE_LOGGING=true
PHONEPE_DEBUG=false
```

## 🚀 Usage

### Setup Subscription

```php
use Kkxdev\PhonePe\Facades\PhonePe;
use Kkxdev\PhonePe\DTO\Subscription\SubscriptionSetupRequest;

$request = SubscriptionSetupRequest::fromArray([
    'merchantOrderId' => 'ORDER_' . time(),
    'amount' => 100000, // Amount in paisa (1000 INR)
    'merchantSubscriptionId' => 'SUB_' . time(),
    'subscriptionType' => 'RECURRING',
    'authWorkflowType' => 'TRANSACTION',
    'amountType' => 'FIXED',
    'maxAmount' => 100000,
    'frequency' => 'MONTHLY', // DAILY, WEEKLY, MONTHLY, QUARTERLY, HALFYEARLY, YEARLY
    'productType' => 'UPI_MANDATE',
    'redirectUrl' => 'https://yourdomain.com/payment/success',
    'cancelRedirectUrl' => 'https://yourdomain.com/payment/cancel',
    'message' => 'Monthly subscription payment',
    'metaInfo' => [
        'udf1' => 'custom_data_1',
        'udf2' => 'custom_data_2',
    ],
]);

try {
    $response = PhonePe::subscription()->setup($request);

    // Redirect user to PhonePe authorization page
    return redirect($response['redirectUrl']);

} catch (\Kkxdev\PhonePe\Exceptions\ValidationException $e) {
    // Handle validation errors
    $errors = $e->getErrors();
} catch (\Kkxdev\PhonePe\Exceptions\ApiException $e) {
    // Handle API errors
    $statusCode = $e->getStatusCode();
    $responseBody = $e->getResponseBody();
}
```

### Check Subscription Status

```php
use Kkxdev\PhonePe\Facades\PhonePe;

$status = PhonePe::subscription()->getStatus('SUB_123456');

if ($status->isActive()) {
    // Subscription is active
    echo "Subscription ID: {$status->subscriptionId}\n";
    echo "State: {$status->state}\n";
    echo "Frequency: {$status->frequency}\n";
    echo "Max Amount: {$status->maxAmount}\n";
} elseif ($status->isCancelled()) {
    // Subscription was cancelled/revoked
} elseif ($status->isPaused()) {
    // Subscription is paused
}
```

### Check Order Status

```php
use Kkxdev\PhonePe\Facades\PhonePe;

$orderStatus = PhonePe::subscription()->getOrderStatus('ORDER_123456');

if ($orderStatus->isCompleted()) {
    // Payment completed successfully
    $transactionId = $orderStatus->paymentDetails[0]['transactionId'] ?? null;
} elseif ($orderStatus->isFailed()) {
    // Payment failed
} elseif ($orderStatus->isPending()) {
    // Payment still pending
}
```

### Notify Redemption (Recurring Payment)

**Important:** Must be called 24 hours before executing redemption.

```php
use Kkxdev\PhonePe\Facades\PhonePe;
use Kkxdev\PhonePe\DTO\Redemption\RedemptionNotifyRequest;

$request = RedemptionNotifyRequest::fromArray([
    'merchantOrderId' => 'REDEMPTION_' . time(),
    'amount' => 100000, // Amount in paisa
    'merchantSubscriptionId' => 'SUB_123456',
    'redemptionRetryStrategy' => 'STANDARD', // STANDARD or CUSTOM
]);

$response = PhonePe::redemption()->notify($request);

// Store orderId for later execution
$orderId = $response['orderId'];
$state = $response['state']; // NOTIFIED
```

### Execute Redemption (Charge Customer)

**Important:** Call this after 24-hour notification period.

```php
use Kkxdev\PhonePe\Facades\PhonePe;
use Kkxdev\PhonePe\DTO\Redemption\RedemptionExecuteRequest;

$request = RedemptionExecuteRequest::fromArray([
    'merchantOrderId' => 'REDEMPTION_123456',
    'idempotencyKey' => 'IDEMPOTENT_KEY_' . time(), // Optional but recommended
]);

$response = PhonePe::redemption()->execute($request);

if ($response['state'] === 'COMPLETED') {
    $transactionId = $response['transactionId'];
    // Payment successful
} elseif ($response['state'] === 'FAILED') {
    // Payment failed
}
```

### Check Redemption Status

```php
use Kkxdev\PhonePe\Facades\PhonePe;

$status = PhonePe::redemption()->getStatus('REDEMPTION_123456');

if ($status->isCompleted()) {
    echo "Transaction ID: {$status->transactionId}\n";
} elseif ($status->isFailed()) {
    echo "Error Code: {$status->errorCode}\n";
}
```

### Cancel Subscription

```php
use Kkxdev\PhonePe\Facades\PhonePe;

PhonePe::subscription()->cancel('SUB_123456');
// Returns void on success (204 No Content)
```

### Create Refund

```php
use Kkxdev\PhonePe\Facades\PhonePe;
use Kkxdev\PhonePe\DTO\Refund\RefundRequest;

$request = RefundRequest::fromArray([
    'merchantRefundId' => 'REFUND_' . time(),
    'originalMerchantOrderId' => 'ORDER_123456',
    'amount' => 50000, // Amount to refund in paisa
]);

$response = PhonePe::refund()->create($request);

$refundId = $response['refundId'];
$state = $response['state']; // PENDING, CONFIRMED, COMPLETED, FAILED
```

### Check Refund Status

```php
use Kkxdev\PhonePe\Facades\PhonePe;

$status = PhonePe::refund()->getStatus('REFUND_123456');

if ($status->isCompleted()) {
    echo "Refund completed successfully\n";
    echo "Amount: {$status->amount}\n";
} elseif ($status->isFailed()) {
    echo "Refund failed: {$status->errorCode}\n";
}
```

## 🔔 Webhook Handling

PhonePe sends webhooks for subscription lifecycle events (pause, unpause, revoke).

### Verify Webhook Signature

```php
use Kkxdev\PhonePe\Facades\PhonePe;
use Illuminate\Http\Request;

public function handleWebhook(Request $request)
{
    try {
        $authHeader = $request->header('Authorization');
        $payload = $request->all();

        // Verify and parse webhook
        $event = PhonePe::verifyWebhook($authHeader, $payload);

        // Handle different event types
        if ($event->isSubscriptionPaused()) {
            $subscriptionId = $event->getMerchantSubscriptionId();
            // Handle pause event

        } elseif ($event->isSubscriptionUnpaused()) {
            $subscriptionId = $event->getMerchantSubscriptionId();
            // Handle unpause event

        } elseif ($event->isSubscriptionRevoked()) {
            $subscriptionId = $event->getMerchantSubscriptionId();
            // Handle revocation (user-initiated cancellation)

        } elseif ($event->isRedemptionCompleted()) {
            // Handle successful redemption

        } elseif ($event->isRedemptionFailed()) {
            // Handle failed redemption
        }

        return response()->json(['status' => 'success'], 200);

    } catch (\Kkxdev\PhonePe\Exceptions\WebhookVerificationException $e) {
        // Signature verification failed
        return response()->json(['error' => 'Invalid signature'], 403);
    }
}
```

## 🔐 Security

### Token Management
- OAuth tokens are automatically cached with TTL
- Tokens refresh 90 seconds before expiry
- Thread-safe caching via Laravel's cache system

### Webhook Verification
- SHA256 signature validation
- Configurable username/password
- Protects against replay attacks

### Idempotency
- Use idempotency keys for redemption execution
- Prevents duplicate charges

## 🛡️ Resilience

### Retry Policy
Automatically retries failed requests with exponential backoff:

```php
// Configured in config/phonepe.php
'retry' => [
    'enabled' => true,
    'max_attempts' => 3,
    'base_delay_ms' => 1000,
    'max_delay_ms' => 10000,
    'jitter' => true, // Prevents thundering herd
],
```

**Retry Triggers:**
- Network timeouts
- Connection failures
- 5xx server errors

**Non-Retryable:**
- 4xx client errors (validation, auth, etc.)

### Circuit Breaker
Protects against cascading failures:

```php
'circuit_breaker' => [
    'enabled' => true,
    'failure_threshold' => 5, // Open after 5 consecutive failures
    'success_threshold' => 2, // Close after 2 successes in half-open
    'cooldown_seconds' => 60, // Wait 60s before testing recovery
],
```

**States:**
- **CLOSED:** Normal operation
- **OPEN:** Blocking requests (service is down)
- **HALF_OPEN:** Testing recovery

## 📊 Subscription Flow Diagram

```
┌─────────┐                                  ┌─────────┐
│ Merchant│                                  │ PhonePe │
└────┬────┘                                  └────┬────┘
     │                                            │
     │ 1. Setup Subscription                     │
     │──────────────────────────────────────────>│
     │                                            │
     │ 2. Return redirectUrl                     │
     │<──────────────────────────────────────────│
     │                                            │
     │ 3. User authorizes via PSP app            │
     │━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━>│
     │                                            │
     │ 4. Redirect back                          │
     │<━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━│
     │                                            │
     │ 5. Check Order Status                     │
     │──────────────────────────────────────────>│
     │                                            │
     │ 6. Return COMPLETED                       │
     │<──────────────────────────────────────────│
     │                                            │
     ├──── Recurring Redemption Cycle ──────────┤
     │                                            │
     │ 7. Notify Redemption                      │
     │──────────────────────────────────────────>│
     │                                            │
     │    Wait 24 hours                          │
     │                                            │
     │ 8. Execute Redemption                     │
     │──────────────────────────────────────────>│
     │                                            │
     │ 9. Return COMPLETED + transactionId       │
     │<──────────────────────────────────────────│
     │                                            │
     └──── Repeat per frequency ────────────────┘
```

## 🧪 Testing

```bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage
```

### Mock HTTP Responses

```php
use Kkxdev\PhonePe\Contracts\HttpClientInterface;

// In your tests
$mockClient = Mockery::mock(HttpClientInterface::class);
$mockClient->shouldReceive('send')
    ->andReturn([
        'orderId' => 'PG_ORDER_123',
        'state' => 'PENDING',
        'redirectUrl' => 'https://phonepe.com/...',
    ]);

$this->app->instance(HttpClientInterface::class, $mockClient);
```

## 🏗️ Architecture

This package follows **SOLID principles** and enterprise design patterns:

- **Adapter Pattern:** HTTP client abstraction
- **Strategy Pattern:** Environment switching (sandbox/production)
- **Factory Pattern:** Versioned API clients
- **DTO Pattern:** Immutable request/response objects
- **Retry Policy Pattern:** Network resilience
- **Circuit Breaker Pattern:** Service protection
- **Facade Pattern:** Developer ergonomics

## 📘 API Reference

### SubscriptionApiInterface

| Method | Description |
|--------|-------------|
| `setup(SubscriptionSetupRequest)` | Create new subscription |
| `getOrderStatus(string)` | Check order status |
| `getStatus(string)` | Check subscription status |
| `cancel(string)` | Cancel active subscription |

### RedemptionApiInterface

| Method | Description |
|--------|-------------|
| `notify(RedemptionNotifyRequest)` | Notify upcoming charge (24h before) |
| `execute(RedemptionExecuteRequest)` | Execute recurring charge |
| `getStatus(string)` | Check redemption status |

### RefundApiInterface

| Method | Description |
|--------|-------------|
| `create(RefundRequest)` | Create refund |
| `getStatus(string)` | Check refund status |

## 🤝 Contributing

Contributions are welcome! Please ensure:
- Code follows PSR-12 standards
- All tests pass
- New features include tests
- Documentation is updated

## 📄 License

MIT License. See [LICENSE](LICENSE) for details.

## 🙏 Support

- [Documentation](https://developer.phonepe.com/payment-gateway/autopay/standard-checkout)
- [Issues](https://github.com/kkxdev/laravel-phonepe-autopay/issues)

---

Built with ❤️ by KKXDev