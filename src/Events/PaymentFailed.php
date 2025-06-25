<?php

namespace Ahmad-Chebbo\LaravelHyperpay\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Payment Failed Event
 */
class PaymentFailed
{
    use Dispatchable, SerializesModels;

    public array $paymentData;

    public string $reason;

    public function __construct(array $paymentData, string $reason = '')
    {
        $this->paymentData = $paymentData;
        $this->reason = $reason;
    }
}
