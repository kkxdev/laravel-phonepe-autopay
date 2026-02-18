<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Resilience\CircuitBreaker;

/**
 * Circuit Breaker States
 */
enum CircuitBreakerState: string
{
    case CLOSED = 'closed';      // Normal operation, requests allowed
    case OPEN = 'open';          // Failing, requests blocked
    case HALF_OPEN = 'half_open'; // Testing recovery, limited requests
}
