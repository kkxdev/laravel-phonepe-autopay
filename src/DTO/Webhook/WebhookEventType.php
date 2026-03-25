<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\DTO\Webhook;

/**
 * PhonePe Webhook Event Type Constants
 *
 * Use these constants to match the `event` field in incoming webhook payloads.
 * The `event` field is the canonical identifier; the older `type` field is deprecated.
 *
 * @see https://developer.phonepe.com/payment-gateway/autopay/webhook
 */
final class WebhookEventType
{
    // Setup events — fired when a subscription's initial payment completes or fails
    public const SUBSCRIPTION_SETUP_ORDER_COMPLETED = 'subscription.setup.order.completed';
    public const SUBSCRIPTION_SETUP_ORDER_FAILED    = 'subscription.setup.order.failed';

    // State-change events — subscription lifecycle transitions
    public const SUBSCRIPTION_PAUSED    = 'subscription.paused';
    public const SUBSCRIPTION_UNPAUSED  = 'subscription.unpaused';
    public const SUBSCRIPTION_REVOKED   = 'subscription.revoked';
    public const SUBSCRIPTION_CANCELLED = 'subscription.cancelled';

    /**
     * @deprecated Present in older integrations; prefer setup/revoked/cancelled events.
     */
    public const SUBSCRIPTION_COMPLETED = 'subscription.completed';

    // Notification events — fired after notify-redemption API calls
    public const SUBSCRIPTION_NOTIFICATION_COMPLETED = 'subscription.notification.completed';
    public const SUBSCRIPTION_NOTIFICATION_FAILED    = 'subscription.notification.failed';

    // Redemption events — recurring charge outcomes
    public const SUBSCRIPTION_REDEMPTION_ORDER_COMPLETED       = 'subscription.redemption.order.completed';
    public const SUBSCRIPTION_REDEMPTION_ORDER_FAILED          = 'subscription.redemption.order.failed';
    public const SUBSCRIPTION_REDEMPTION_TRANSACTION_COMPLETED = 'subscription.redemption.transaction.completed';
    public const SUBSCRIPTION_REDEMPTION_TRANSACTION_FAILED    = 'subscription.redemption.transaction.failed';

    // Refund events
    public const PG_REFUND_ACCEPTED = 'pg.refund.accepted';
    public const PG_REFUND_COMPLETED = 'pg.refund.completed';
    public const PG_REFUND_FAILED    = 'pg.refund.failed';

    private function __construct() {}
}