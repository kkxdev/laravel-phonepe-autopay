# PhonePe Laravel Package - Installation Guide

## 📦 Local Package Installation

Since this package is in your monorepo at `packages/laravel-phonepe-autopay/`, follow these steps to install it in your main Laravel application.

## Step 1: Update Main composer.json

Add the local package repository to your main `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/laravel-phonepe-autopay",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "kkxdev/laravel-phonepe-autopay": "@dev"
    }
}
```

## Step 2: Install the Package

```bash
composer update kkxdev/laravel-phonepe-autopay
```

## Step 3: Publish Configuration

```bash
php artisan vendor:publish --tag=phonepe-config
```

This will create `config/phonepe.php`.

## Step 4: Configure Environment Variables

Add these to your `.env`:

```env
# PhonePe Environment
PHONEPE_ENV=sandbox

# PhonePe Credentials (from PhonePe Dashboard)
PHONEPE_MERCHANT_ID=your_merchant_id
PHONEPE_CLIENT_ID=your_client_id
PHONEPE_CLIENT_SECRET=your_client_secret
PHONEPE_CLIENT_VERSION=v1

# Redirect URLs
PHONEPE_SUCCESS_URL=https://yourdomain.com/phonepe/success
PHONEPE_FAILURE_URL=https://yourdomain.com/phonepe/failure

# Webhook Configuration
PHONEPE_WEBHOOK_USERNAME=your_webhook_username
PHONEPE_WEBHOOK_PASSWORD=your_webhook_password

# Optional: Resilience Settings
PHONEPE_RETRY_ENABLED=true
PHONEPE_RETRY_MAX_ATTEMPTS=3
PHONEPE_RETRY_BASE_DELAY=1000
PHONEPE_CIRCUIT_BREAKER_ENABLED=true
PHONEPE_CIRCUIT_BREAKER_THRESHOLD=5
PHONEPE_CIRCUIT_BREAKER_COOLDOWN=60

# Optional: Logging
PHONEPE_LOGGING=true
PHONEPE_DEBUG=false
```

## Step 5: Clear Config Cache

```bash
php artisan config:clear
php artisan config:cache
```

## Step 6: Test the Installation

Create a test route to verify installation:

```php
// routes/web.php
use Kkxdev\PhonePe\Facades\PhonePe;

Route::get('/phonepe/test', function () {
    try {
        $version = PhonePe::getVersion();
        return "PhonePe SDK v{$version} installed successfully!";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});
```

Visit: `http://yourdomain.com/phonepe/test`

## Step 7: Implement Subscription Flow

### 7.1 Create Subscription Controller

```bash
php artisan make:controller PhonePe/SubscriptionController
```

```php
<?php

namespace App\Http\Controllers\PhonePe;

use App\Http\Controllers\Controller;
use Kkxdev\PhonePe\Facades\PhonePe;
use Kkxdev\PhonePe\DTO\Subscription\SubscriptionSetupRequest;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|string',
            'amount' => 'required|integer|min:100',
            'subscription_id' => 'required|string',
            'frequency' => 'required|in:DAILY,WEEKLY,MONTHLY,QUARTERLY,YEARLY',
        ]);

        try {
            $setupRequest = SubscriptionSetupRequest::fromArray([
                'merchantOrderId' => $validated['order_id'],
                'amount' => $validated['amount'],
                'merchantSubscriptionId' => $validated['subscription_id'],
                'subscriptionType' => 'RECURRING',
                'authWorkflowType' => 'TRANSACTION',
                'amountType' => 'FIXED',
                'maxAmount' => $validated['amount'],
                'frequency' => $validated['frequency'],
                'productType' => 'UPI_MANDATE',
                'redirectUrl' => config('phonepe.urls.sandbox.redirect_success'),
                'cancelRedirectUrl' => config('phonepe.urls.sandbox.redirect_failure'),
            ]);

            $response = PhonePe::subscription()->setup($setupRequest);

            // Store orderId in database
            // ...

            return redirect($response['redirectUrl']);

        } catch (\Auw\PhonePe\Exceptions\ValidationException $e) {
            return back()->withErrors(['phonepe' => $e->getMessage()]);
        } catch (\Auw\PhonePe\Exceptions\ApiException $e) {
            return back()->withErrors(['phonepe' => $e->getMessage()]);
        }
    }

    public function success(Request $request)
    {
        $orderId = $request->query('orderId');

        try {
            $status = PhonePe::subscription()->getOrderStatus($orderId);

            if ($status->isCompleted()) {
                // Update database
                // Activate subscription
                return view('phonepe.success', compact('status'));
            }

            return view('phonepe.pending', compact('status'));

        } catch (\Exception $e) {
            return view('phonepe.error', ['error' => $e->getMessage()]);
        }
    }

    public function failure(Request $request)
    {
        $orderId = $request->query('orderId');

        return view('phonepe.failure', compact('orderId'));
    }
}
```

### 7.2 Add Routes

```php
// routes/web.php
use App\Http\Controllers\PhonePe\SubscriptionController;

Route::prefix('phonepe')->name('phonepe.')->group(function () {
    Route::post('/subscription/create', [SubscriptionController::class, 'create'])
        ->name('subscription.create');
    Route::get('/success', [SubscriptionController::class, 'success'])
        ->name('success');
    Route::get('/failure', [SubscriptionController::class, 'failure'])
        ->name('failure');
});
```

## Step 8: Implement Webhook Handler

```bash
php artisan make:controller PhonePe/WebhookController
```

```php
<?php

namespace App\Http\Controllers\PhonePe;

use App\Http\Controllers\Controller;
use Kkxdev\PhonePe\Facades\PhonePe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $authHeader = $request->header('Authorization');
            $payload = $request->all();

            // Verify signature
            $event = PhonePe::verifyWebhook($authHeader, $payload);

            Log::info('PhonePe Webhook Received', [
                'type' => $event->type,
                'subscription_id' => $event->getMerchantSubscriptionId(),
                'state' => $event->getState(),
            ]);

            // Handle different event types
            if ($event->isSubscriptionPaused()) {
                $this->handleSubscriptionPaused($event);
            } elseif ($event->isSubscriptionUnpaused()) {
                $this->handleSubscriptionUnpaused($event);
            } elseif ($event->isSubscriptionRevoked()) {
                $this->handleSubscriptionRevoked($event);
            } elseif ($event->isRedemptionCompleted()) {
                $this->handleRedemptionCompleted($event);
            } elseif ($event->isRedemptionFailed()) {
                $this->handleRedemptionFailed($event);
            }

            return response()->json(['status' => 'success'], 200);

        } catch (\Auw\PhonePe\Exceptions\WebhookVerificationException $e) {
            Log::error('PhonePe Webhook Verification Failed', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 403);
        } catch (\Exception $e) {
            Log::error('PhonePe Webhook Error', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    private function handleSubscriptionPaused($event): void
    {
        // Update subscription status in database
        // Pause recurring charges
    }

    private function handleSubscriptionUnpaused($event): void
    {
        // Update subscription status in database
        // Resume recurring charges
    }

    private function handleSubscriptionRevoked($event): void
    {
        // Update subscription status in database
        // Cancel subscription permanently
    }

    private function handleRedemptionCompleted($event): void
    {
        // Record successful payment
        // Update user balance/credits
    }

    private function handleRedemptionFailed($event): void
    {
        // Handle failed payment
        // Notify user
        // Retry logic
    }
}
```

### Add Webhook Route

```php
// routes/api.php
use App\Http\Controllers\PhonePe\WebhookController;

Route::post('/phonepe/webhook', [WebhookController::class, 'handle'])
    ->name('phonepe.webhook');
```

**Important:** Exclude webhook route from CSRF protection in `app/Http/Middleware/VerifyCsrfToken.php`:

```php
protected $except = [
    'api/phonepe/webhook',
];
```

## Step 9: Implement Recurring Payments

```bash
php artisan make:command PhonePe/ProcessRecurringPayments
```

```php
<?php

namespace App\Console\Commands\PhonePe;

use Illuminate\Console\Command;
use Kkxdev\PhonePe\Facades\PhonePe;
use Kkxdev\PhonePe\DTO\Redemption\RedemptionNotifyRequest;
use Kkxdev\PhonePe\DTO\Redemption\RedemptionExecuteRequest;

class ProcessRecurringPayments extends Command
{
    protected $signature = 'phonepe:process-recurring';
    protected $description = 'Process PhonePe recurring payments';

    public function handle()
    {
        // Get subscriptions due for payment
        $subscriptions = $this->getSubscriptionsDueForPayment();

        foreach ($subscriptions as $subscription) {
            try {
                // Step 1: Notify (24 hours before charge)
                if ($subscription->needs_notification) {
                    $this->notifyRedemption($subscription);
                }

                // Step 2: Execute (after 24 hours)
                if ($subscription->ready_for_execution) {
                    $this->executeRedemption($subscription);
                }

            } catch (\Exception $e) {
                $this->error("Error processing subscription {$subscription->id}: {$e->getMessage()}");
            }
        }

        $this->info('Recurring payments processed successfully!');
    }

    private function notifyRedemption($subscription)
    {
        $request = RedemptionNotifyRequest::fromArray([
            'merchantOrderId' => 'REDEMPTION_' . time() . '_' . $subscription->id,
            'amount' => $subscription->amount,
            'merchantSubscriptionId' => $subscription->phonepe_subscription_id,
            'redemptionRetryStrategy' => 'STANDARD',
        ]);

        $response = PhonePe::redemption()->notify($request);

        // Update database
        $subscription->update([
            'last_notification_at' => now(),
            'phonepe_order_id' => $response['orderId'],
            'notification_state' => $response['state'],
        ]);

        $this->info("Notified redemption for subscription {$subscription->id}");
    }

    private function executeRedemption($subscription)
    {
        $request = RedemptionExecuteRequest::fromArray([
            'merchantOrderId' => $subscription->phonepe_order_id,
            'idempotencyKey' => 'EXEC_' . $subscription->id . '_' . time(),
        ]);

        $response = PhonePe::redemption()->execute($request);

        // Update database based on response
        $subscription->update([
            'last_charge_at' => now(),
            'execution_state' => $response['state'],
            'transaction_id' => $response['transactionId'] ?? null,
        ]);

        if ($response['state'] === 'COMPLETED') {
            $this->info("Successfully charged subscription {$subscription->id}");
        } else {
            $this->warn("Charge pending/failed for subscription {$subscription->id}");
        }
    }

    private function getSubscriptionsDueForPayment()
    {
        // Implement your logic to fetch subscriptions
        return [];
    }
}
```

### Schedule the Command

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Run every hour to check for due payments
    $schedule->command('phonepe:process-recurring')
        ->hourly()
        ->withoutOverlapping();
}
```

## Step 10: Testing

### Test in Sandbox Mode

1. Ensure `PHONEPE_ENV=sandbox` in `.env`
2. Use PhonePe test credentials
3. Test complete subscription flow
4. Test webhook handling
5. Test recurring payments

### Test Checklist

- [ ] Subscription setup redirects to PhonePe
- [ ] User can authorize payment
- [ ] Success callback receives correct status
- [ ] Webhook signature verification works
- [ ] Webhook events are handled correctly
- [ ] Recurring payments notify 24h before
- [ ] Recurring payments execute successfully
- [ ] Refunds process correctly
- [ ] Error handling works as expected
- [ ] Logs are generated (check `storage/logs/`)

## Step 11: Production Deployment

1. Switch to production mode:
   ```env
   PHONEPE_ENV=production
   ```

2. Update credentials to production values

3. Update redirect URLs to production URLs

4. Configure webhook URL in PhonePe Dashboard:
   ```
   https://yourdomain.com/api/phonepe/webhook
   ```

5. Enable monitoring and logging

6. Test in production with small amounts first

## 🔧 Troubleshooting

### Issue: "OAuth token fetch failed"
**Solution:** Check client credentials in `.env`

### Issue: "Webhook signature verification failed"
**Solution:** Verify webhook username/password match PhonePe Dashboard

### Issue: "Circuit breaker is OPEN"
**Solution:** PhonePe API is experiencing issues. Wait for cooldown period (60s default)

### Issue: "Connection timeout"
**Solution:** Increase timeout values in config

### Enable Debug Logging
```env
PHONEPE_DEBUG=true
PHONEPE_LOG_CHANNEL=daily
```

Check logs: `storage/logs/laravel-{date}.log`

## 📚 Additional Resources

- [PhonePe API Documentation](https://developer.phonepe.com/payment-gateway/autopay/standard-checkout)
- [Package README](README.md)
- [Implementation Summary](IMPLEMENTATION_SUMMARY.md)

## 🎉 You're Done!

Your PhonePe integration is now complete. Start accepting recurring payments!

---

**Need Help?**
- Check the logs for detailed error messages
- Review the package README for usage examples
- Check PhonePe Dashboard for transaction status
