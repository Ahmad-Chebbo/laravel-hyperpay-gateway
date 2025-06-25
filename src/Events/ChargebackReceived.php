<?php

namespace AhmadShebbo\LaravelHyperpay\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Chargeback Received Event
 */
class ChargebackReceived
{
    use Dispatchable, SerializesModels;

    public array $chargebackData;

    public function __construct(array $chargebackData)
    {
        $this->chargebackData = $chargebackData;
    }
}
