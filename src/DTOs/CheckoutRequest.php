<?php

namespace AhmadChebbo\LaravelHyperpay\DTOs;

use AhmadChebbo\LaravelHyperpay\DTOs\CustomerData;
use AhmadChebbo\LaravelHyperpay\DTOs\BillingData;
use AhmadChebbo\LaravelHyperpay\DTOs\ShippingData;

/**
 * Checkout Request DTO
 */
class CheckoutRequest
{
    public function __construct(
        public float $amount,
        public string $brand,
        public ?string $currency = null,
        public ?string $paymentType = null,
        public ?string $merchantTransactionId = null,
        public ?CustomerData $customer = null,
        public ?BillingData $billing = null,
        public ?ShippingData $shipping = null,
        public ?array $customParameters = null,
        public ?array $riskParameters = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'amount' => $this->amount,
            'brand' => $this->brand,
            'currency' => $this->currency,
            'paymentType' => $this->paymentType,
            'merchantTransactionId' => $this->merchantTransactionId,
        ], fn($value) => $value !== null);
    }
}
