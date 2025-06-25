<?php

namespace AhmadChebbo\LaravelHyperpay\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


/**
 * Refund Processed Event
 */
class RefundProcessed
{
    use Dispatchable, SerializesModels;

    public array $refundData;

    public function __construct(array $refundData)
    {
        $this->refundData = $refundData;
    }
}