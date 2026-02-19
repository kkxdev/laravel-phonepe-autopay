# Changelog

All notable changes to `laravel-phonepe-autopay` will be documented in this file.

## [1.0.0] - 2025-01-19

### Added
- Initial release of PhonePe AutoPay Laravel SDK
- Complete PhonePe Payment Gateway integration with AutoPay (Recurring Payments) support
- Authentication API with OAuth token management and auto-refresh
- Subscription lifecycle management:
    - Setup subscription with auto-debit authorization
    - Check subscription status
    - Check order status
    - Cancel subscription
    - Pause subscription
    - Revoke subscription
- Redemption (recurring charge) management:
    - Notify redemption (24-hour notice)
    - Execute redemption
    - Check redemption status
- Refund management:
    - Create refund
    - Check refund status
- Webhook handler with signature verification
- Enterprise resilience patterns:
    - Exponential backoff retry policy with jitter
    - Circuit breaker pattern (CLOSED/OPEN/HALF_OPEN states)
    - Configurable failure thresholds and cooldown periods
- HTTP client abstraction with Guzzle adapter
- DTO pattern for type-safe data transfer
- Service layer architecture
- Token caching with TTL management
- Environment resolver (sandbox/production)
- Comprehensive exception handling:
    - PhonePeException (base)
    - AuthenticationException
    - ApiException
    - NetworkException
    - ValidationException
    - WebhookVerificationException
    - CircuitBreakerException
- Laravel service provider with auto-discovery
- Facade support for easy access
- PSR-4 autoloading
- Comprehensive configuration file
- Full test suite

### Supported Endpoints
- POST `/v1/recurring/auth/token` - OAuth token
- POST `/v1/recurring/subscription/setup` - Setup subscription
- GET `/v1/recurring/subscription/status/{merchantSubscriptionId}` - Subscription status
- GET `/v1/recurring/order/status/{merchantOrderId}` - Order status
- POST `/v1/recurring/subscription/cancel/{merchantSubscriptionId}` - Cancel subscription
- POST `/v1/recurring/subscription/pause/{merchantSubscriptionId}` - Pause subscription
- POST `/v1/recurring/subscription/revoke/{merchantSubscriptionId}` - Revoke subscription
- POST `/v1/recurring/redemption/notify` - Notify redemption
- POST `/v1/recurring/redemption/execute` - Execute redemption
- GET `/v1/recurring/redemption/status/{merchantOrderId}` - Redemption status
- POST `/v1/recurring/refund` - Create refund
- GET `/v1/recurring/refund/status/{merchantRefundId}` - Refund status

### Requirements
- PHP 8.0 or higher
- Laravel 8.x through 12.x
- Guzzle HTTP Client (via Laravel)
- PSR Log Interface

### Design Patterns Implemented
1. **Adapter Pattern** - HTTP client abstraction
2. **Strategy Pattern** - Retry policies
3. **Factory Pattern** - DTO creation
4. **Interface Segregation** - API contracts
5. **Service Layer Pattern** - Business logic encapsulation
6. **Repository Pattern** - Data access (for application integration)
7. **Circuit Breaker Pattern** - Fault tolerance
8. **Builder Pattern** - Endpoint construction
9. **Facade Pattern** - Laravel integration
10. **DTO Pattern** - Type-safe data transfer
11. **Dependency Injection** - Constructor injection throughout

### Documentation
- Complete README with usage examples
- Installation guide
- Implementation summary
- Update summary
- API reference
- Configuration guide
- Testing guide

### License
MIT License

---

## Release Notes

### v1.0.0 - Initial Production Release

This is the first stable release of the PhonePe AutoPay Laravel SDK. It provides a complete, production-ready integration with PhonePe Payment Gateway's AutoPay (Recurring Payments) system.

**Key Features:**
- ✅ Full API coverage for PhonePe AutoPay
- ✅ Enterprise-grade resilience patterns
- ✅ Type-safe DTOs
- ✅ Comprehensive exception handling
- ✅ OAuth token auto-management
- ✅ Webhook signature verification
- ✅ PSR-compliant logging
- ✅ Laravel 8.x - 12.x support
- ✅ PHP 8.0 - 8.4 support

**Compatibility:**
- Laravel: 8.x, 9.x, 10.x, 11.x, 12.x
- PHP: 8.0, 8.1, 8.2, 8.3, 8.4

**Installation:**
```bash
composer require kkxdev/laravel-phonepe-autopay
```

**Quick Start:**
```php
use Kkxdev\PhonePe\Api\V1\SubscriptionApi;

$subscriptionApi = app(SubscriptionApi::class);
$response = $subscriptionApi->setupSubscription($request);
```

For detailed documentation, see [README.md](README.md).

