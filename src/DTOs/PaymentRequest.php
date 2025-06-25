<?php

namespace AhmadChebbo\LaravelHyperpay\DTOs;

/**
 * Payment Request DTO
 */
class PaymentRequest
{
    public function __construct(
        public float $amount,
        public string $brand,
        public string $cardNumber,
        public string $cardHolder,
        public string $expiryMonth,
        public string $expiryYear,
        public string $cvv,
        public ?string $currency = null,
        public ?string $paymentType = null,
        public ?string $merchantTransactionId = null,
        public ?string $shopperResultUrl = null,
        public ?CustomerData $customer = null,
        public ?BillingData $billing = null,
        public ?ShippingData $shipping = null,
        public ?array $threeDSecure = null,
        public ?array $customParameters = null,
        public ?array $riskParameters = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'amount' => $this->amount,
            'brand' => $this->brand,
            'cardNumber' => $this->cardNumber,
            'cardHolder' => $this->cardHolder,
            'expiryMonth' => $this->expiryMonth,
            'expiryYear' => $this->expiryYear,
            'cvv' => $this->cvv,
            'currency' => $this->currency,
            'paymentType' => $this->paymentType,
            'merchantTransactionId' => $this->merchantTransactionId,
            'shopperResultUrl' => $this->shopperResultUrl,
        ], fn ($value) => $value !== null);
    }
}
