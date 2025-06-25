<?php

namespace AhmadChebbo\LaravelHyperpay\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Payment Pending Event
 */
class PaymentPending
{
    use Dispatchable, SerializesModels;

    public array $paymentData;

    public function __construct(array $paymentData)
    {
        $this->paymentData = $paymentData;
    }
}
