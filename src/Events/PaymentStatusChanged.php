<?php

namespace AhmadChebbo\LaravelHyperpay\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Payment Status Changed Event
 */
class PaymentStatusChanged
{
    use Dispatchable, SerializesModels;

    public array $paymentData;

    public function __construct(array $paymentData)
    {
        $this->paymentData = $paymentData;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentData['paymentId'] ?? null;
    }

    public function getResultCode(): ?string
    {
        return $this->paymentData['result']['code'] ?? null;
    }

    public function isSuccessful(): bool
    {
        $code = $this->getResultCode();
        return $code && app('hyperpay.result')->isSuccessful($code);
    }

    public function isPending(): bool
    {
        $code = $this->getResultCode();
        return $code && app('hyperpay.result')->isPending($code);
    }

    public function isRejected(): bool
    {
        $code = $this->getResultCode();
        return $code && app('hyperpay.result')->isRejected($code);
    }

    public function getMerchantTransactionId(): ?string
    {
        return $this->paymentData['merchantTransactionId'] ?? null;
    }

    public function getAmount(): ?string
    {
        return $this->paymentData['amount'] ?? null;
    }

    public function getPaymentBrand(): ?string
    {
        return $this->paymentData['paymentBrand'] ?? null;
    }
}