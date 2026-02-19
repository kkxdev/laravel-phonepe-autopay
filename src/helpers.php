<?php

declare(strict_types=1);

if (!function_exists('phonepe_success_url')) {
    /**
     * Get PhonePe success redirect URL for current environment
     *
     * @return string
     */
    function phonepe_success_url(): string
    {
        return app('phonepe')->getSuccessUrl();
    }
}

if (!function_exists('phonepe_failure_url')) {
    /**
     * Get PhonePe failure redirect URL for current environment
     *
     * @return string
     */
    function phonepe_failure_url(): string
    {
        return app('phonepe')->getFailureUrl();
    }
}
