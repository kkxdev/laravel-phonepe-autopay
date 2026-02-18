<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Facades;

use Kkxdev\PhonePe\Contracts\AuthApiInterface;
use Kkxdev\PhonePe\Contracts\RedemptionApiInterface;
use Kkxdev\PhonePe\Contracts\RefundApiInterface;
use Kkxdev\PhonePe\Contracts\SubscriptionApiInterface;
use Kkxdev\PhonePe\Contracts\WebhookVerifierInterface;
use Kkxdev\PhonePe\DTO\Webhook\WebhookEvent;
use Illuminate\Support\Facades\Facade;

/**
 * PhonePe Facade
 *
 * @method static AuthApiInterface auth()
 * @method static SubscriptionApiInterface subscription()
 * @method static RedemptionApiInterface redemption()
 * @method static RefundApiInterface refund()
 * @method static WebhookVerifierInterface webhook()
 * @method static WebhookEvent verifyWebhook(string $authHeader, array $payload)
 * @method static string getVersion()
 *
 * @see \Auw\PhonePe\PhonePeManager
 */
class PhonePe extends Facade
{
    /**
     * Get the registered name of the component
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'phonepe';
    }
}
