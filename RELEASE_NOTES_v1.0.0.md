# Release Notes - v1.0.0

## 🚀 PhonePe AutoPay Laravel SDK - Initial Release

We're excited to announce the first stable release of the **PhonePe AutoPay Laravel SDK** - a production-grade, enterprise-ready PHP package for integrating PhonePe's AutoPay (Recurring Payments) system into Laravel applications.

---

## 📦 What's Included

### Core Features

#### 🔐 Authentication
- **OAuth 2.0 Token Management**: Automatic token fetch, caching, and refresh
- **Token Cache**: Built-in caching with configurable TTL (default: 3600s)
- **Seamless Integration**: Tokens automatically injected into API requests

#### 💳 Subscription Management
- **Setup Subscription**: Initiate auto-debit authorization with customer
- **Status Tracking**: Real-time subscription and order status checks
- **Lifecycle Control**: Cancel, pause, and revoke subscriptions
- **Flexible Frequencies**: Support for DAILY, WEEKLY, MONTHLY, QUARTERLY, YEARLY, and more

#### 🔄 Redemption (Recurring Charges)
- **Notify Redemption**: Send 24-hour advance notice to customers (PhonePe requirement)
- **Execute Redemption**: Charge customer after notification period
- **Status Monitoring**: Track redemption execution status

#### 💰 Refund Management
- **Create Refund**: Full or partial refund support
- **Status Tracking**: Monitor refund processing status
- **Validation**: Automatic refund amount validation

#### 🔗 Webhook Support
- **Signature Verification**: SHA-256 signature validation for security
- **Event Handling**: Process subscription, redemption, and refund events
- **Type-Safe Events**: Strongly typed webhook event DTOs

---

## 🏗️ Architecture & Design Patterns

This SDK implements **11 enterprise design patterns** for maintainability and scalability:

1. **Adapter Pattern** - HTTP client abstraction (supports Guzzle, future: PSR-18)
2. **Strategy Pattern** - Configurable retry policies
3. **Factory Pattern** - DTO instantiation
4. **Interface Segregation** - Separate API contracts (Auth, Subscription, Redemption, Refund)
5. **Service Layer Pattern** - Business logic encapsulation
6. **Circuit Breaker Pattern** - Fault tolerance with 3 states (CLOSED/OPEN/HALF_OPEN)
7. **Builder Pattern** - Fluent endpoint URL construction
8. **Facade Pattern** - Laravel-style API access
9. **DTO Pattern** - Type-safe, immutable data transfer objects
10. **Dependency Injection** - Constructor injection throughout
11. **Repository Pattern** - Ready for application-level data persistence

---

## 🛡️ Resilience & Reliability

### Retry Policy
- **Exponential Backoff**: Automatic retry with increasing delays
- **Jitter**: Random delay variation to prevent thundering herd
- **Configurable**: Set max attempts, base delay, and backoff multiplier
- **Smart Retry**: Only retries on transient failures (network, timeout, 5xx)

```php
'retry' => [
    'enabled' => true,
    'max_attempts' => 3,
    'base_delay_ms' => 1000,
    'max_delay_ms' => 10000,
    'backoff_multiplier' => 2,
    'jitter' => true,
],
```

### Circuit Breaker
- **Failure Threshold**: Opens circuit after N consecutive failures
- **Cooldown Period**: Waits before attempting recovery
- **Half-Open State**: Gradually tests service health
- **Fail-Fast**: Prevents cascading failures

```php
'circuit_breaker' => [
    'enabled' => true,
    'failure_threshold' => 5,
    'cooldown_seconds' => 60,
],
```

---

## 📚 Complete API Coverage

### Supported Endpoints (12 total)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/v1/recurring/auth/token` | OAuth token |
| POST | `/v1/recurring/subscription/setup` | Setup subscription |
| GET | `/v1/recurring/subscription/status/{id}` | Check subscription status |
| GET | `/v1/recurring/order/status/{id}` | Check order status |
| POST | `/v1/recurring/subscription/cancel/{id}` | Cancel subscription |
| POST | `/v1/recurring/subscription/pause/{id}` | Pause subscription |
| POST | `/v1/recurring/subscription/revoke/{id}` | Revoke subscription |
| POST | `/v1/recurring/redemption/notify` | Notify upcoming charge |
| POST | `/v1/recurring/redemption/execute` | Execute charge |
| GET | `/v1/recurring/redemption/status/{id}` | Check redemption status |
| POST | `/v1/recurring/refund` | Create refund |
| GET | `/v1/recurring/refund/status/{id}` | Check refund status |

---

## 🔧 Installation

### Requirements
- **PHP**: 8.0, 8.1, 8.2, 8.3, or 8.4
- **Laravel**: 8.x, 9.x, 10.x, 11.x, or 12.x
- **Guzzle**: (included with Laravel)

### Install via Composer

```bash
composer require kkxdev/laravel-phonepe-autopay
```

### Publish Configuration

```bash
php artisan vendor:publish --provider="Kkxdev\PhonePe\Providers\PhonePeServiceProvider"
```

### Environment Variables

```env
PHONEPE_ENV=sandbox
PHONEPE_MERCHANT_ID=your_merchant_id
PHONEPE_CLIENT_ID=your_client_id
PHONEPE_CLIENT_SECRET=your_client_secret
PHONEPE_CLIENT_VERSION=v1
PHONEPE_WEBHOOK_SECRET=your_webhook_secret
```

---

## 🚀 Quick Start

### 1. Setup Subscription

```php
use Kkxdev\PhonePe\Api\V1\SubscriptionApi;
use Kkxdev\PhonePe\DTO\Subscription\SubscriptionSetupRequest;

$subscriptionApi = app(SubscriptionApi::class);

$request = new SubscriptionSetupRequest(
    merchantSubscriptionId: 'SUB_' . uniqid(),
    merchantOrderId: 'ORD_' . uniqid(),
    amount: 10000, // in paisa (₹100)
    maxAmount: 50000, // in paisa (₹500)
    frequency: 'MONTHLY',
    redirectUrl: 'https://yourapp.com/payment/success',
    callbackUrl: 'https://yourapp.com/webhook/phonepe',
    mobileNumber: '9876543210'
);

$response = $subscriptionApi->setupSubscription($request);

// Redirect user to PhonePe for authorization
return redirect($response->redirectUrl);
```

### 2. Check Status

```php
$status = $subscriptionApi->checkSubscriptionStatus('SUB_123456');

if ($status->status === 'ACTIVE') {
    // Subscription is active, can charge customer
}
```

### 3. Notify & Execute Redemption

```php
use Kkxdev\PhonePe\Api\V1\RedemptionApi;
use Kkxdev\PhonePe\DTO\Redemption\RedemptionNotifyRequest;

$redemptionApi = app(RedemptionApi::class);

// Step 1: Notify (24 hours before charge)
$notifyRequest = new RedemptionNotifyRequest(
    merchantSubscriptionId: 'SUB_123456',
    merchantOrderId: 'ORD_' . uniqid(),
    amount: 10000,
    message: 'Your monthly subscription will be charged'
);

$redemptionApi->notifyRedemption($notifyRequest);

// Step 2: Execute (after 24 hours)
$executeRequest = new RedemptionExecuteRequest(
    merchantOrderId: 'ORD_123456'
);

$result = $redemptionApi->executeRedemption($executeRequest);
```

### 4. Create Refund

```php
use Kkxdev\PhonePe\Api\V1\RefundApi;
use Kkxdev\PhonePe\DTO\Refund\RefundRequest;

$refundApi = app(RefundApi::class);

$request = new RefundRequest(
    merchantRefundId: 'REF_' . uniqid(),
    originalTransactionId: 'PHONEPE_TXN_123456',
    amount: 5000, // partial refund of ₹50
    reason: 'Customer request'
);

$refund = $refundApi->createRefund($request);
```

### 5. Handle Webhooks

```php
use Kkxdev\PhonePe\Support\WebhookVerifier;

Route::post('/webhook/phonepe', function (Request $request) {
    $verifier = app(WebhookVerifier::class);
    $signature = $request->header('X-PhonePe-Signature');

    if (!$verifier->verify($request->all(), $signature)) {
        return response()->json(['error' => 'Invalid signature'], 401);
    }

    // Process webhook event
    $event = $request->input('event_type');

    match ($event) {
        'SUBSCRIPTION_SETUP_SUCCESS' => handleSubscriptionSuccess($request),
        'REDEMPTION_SUCCESS' => handleRedemptionSuccess($request),
        'REFUND_SUCCESS' => handleRefundSuccess($request),
        default => Log::warning('Unknown webhook event', ['event' => $event]),
    };

    return response()->json(['status' => 'ok']);
});
```

---

## 🧪 Testing

The package includes comprehensive test coverage:

```bash
cd packages/laravel-phonepe-autopay
composer test
```

### Test Categories
- Unit tests for all DTOs
- Integration tests for API endpoints
- Mock tests for resilience patterns
- Webhook verification tests

---

## 🛠️ Configuration Options

Full configuration available in `config/phonepe.php`:

```php
return [
    'environment' => env('PHONEPE_ENV', 'sandbox'),

    'credentials' => [
        'merchant_id' => env('PHONEPE_MERCHANT_ID'),
        'client_id' => env('PHONEPE_CLIENT_ID'),
        'client_secret' => env('PHONEPE_CLIENT_SECRET'),
        'client_version' => env('PHONEPE_CLIENT_VERSION', 'v1'),
    ],

    'resilience' => [
        'retry' => [
            'enabled' => true,
            'max_attempts' => 3,
            'base_delay_ms' => 1000,
            'max_delay_ms' => 10000,
            'backoff_multiplier' => 2,
            'jitter' => true,
        ],
        'circuit_breaker' => [
            'enabled' => true,
            'failure_threshold' => 5,
            'cooldown_seconds' => 60,
        ],
    ],

    'token_cache' => [
        'enabled' => true,
        'key' => 'phonepe_auth_token',
        'ttl_seconds' => 3600,
    ],

    'webhook' => [
        'secret' => env('PHONEPE_WEBHOOK_SECRET'),
        'verify_signature' => true,
    ],

    'logging' => [
        'enabled' => true,
        'channel' => env('PHONEPE_LOG_CHANNEL', 'stack'),
        'level' => env('PHONEPE_LOG_LEVEL', 'info'),
    ],
];
```

---

## 📖 Documentation

- **README.md** - Complete package documentation
- **INSTALLATION_GUIDE.md** - Step-by-step installation
- **IMPLEMENTATION_SUMMARY.md** - Architecture overview
- **UPDATE_SUMMARY.md** - Migration guide
- **CHANGELOG.md** - Version history

---

## 🔒 Exception Handling

All exceptions extend `PhonePeException` with context:

```php
use Kkxdev\PhonePe\Exceptions\PhonePeException;

try {
    $response = $subscriptionApi->setupSubscription($request);
} catch (AuthenticationException $e) {
    // OAuth token fetch failed
    Log::error('PhonePe auth failed', $e->getContext());
} catch (ApiException $e) {
    // PhonePe API returned error
    Log::error('PhonePe API error', [
        'status' => $e->getStatusCode(),
        'context' => $e->getContext(),
    ]);
} catch (NetworkException $e) {
    // Network/timeout error
    Log::error('PhonePe network error', $e->getContext());
} catch (CircuitBreakerException $e) {
    // Circuit is open, fail fast
    Log::warning('PhonePe circuit breaker open');
} catch (PhonePeException $e) {
    // Generic PhonePe error
    Log::error('PhonePe error', $e->getContext());
}
```

---

## 🎯 Use Cases

This SDK is perfect for:

- 🎓 **EdTech platforms** with monthly course subscriptions
- 📰 **News/Media** with recurring content access
- 🎵 **Music/Video streaming** with subscription plans
- 📦 **SaaS products** with tiered pricing
- 🏋️ **Fitness apps** with membership billing
- 📚 **E-learning** with periodic payments
- 🎮 **Gaming platforms** with premium subscriptions
- 🛒 **E-commerce** with subscription boxes

---

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Add tests for new features
4. Submit a pull request

---

## 📄 License

MIT License - see [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

Built with ❤️ by **kkxdev**

Special thanks to:
- PhonePe for comprehensive API documentation
- Laravel community for framework support
- All contributors and testers

---

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/kkxdev/laravel-phonepe-autopay/issues)
- **Documentation**: [README.md](README.md)
- **Email**: support@kkxdev.com

---

## 🗺️ Roadmap

Future enhancements planned:

- [ ] PSR-18 HTTP client support
- [ ] Async/Promise support
- [ ] Event sourcing
- [ ] Multi-merchant support
- [ ] Advanced reporting
- [ ] GraphQL API wrapper
- [ ] Admin dashboard

---

## ⭐ Star Us!

If you find this package useful, please give it a star on GitHub!

**Happy Coding!** 🚀
