<?php

namespace Ahmad-Chebbo\LaravelHyperpay\DTOs;

/**
 * Payment Request DTO
 */
class PaymentRequest
{
    public function __construct(
        public float $amount,
        public string $brand,
        public ?string $cardNumber = null,
        public ?string $cardHolder = null,
        public ?int $expiryMonth = null,
        public ?int $expiryYear = null,
        public ?string $cvv = null,
        public ?string $currency = null,
        public ?string $paymentType = null,
        public ?string $merchantTransactionId = null,
        public ?CustomerData $customer = null,
        public ?BillingData $billing = null,
        public ?ShippingData $shipping = null,
        public ?array $threeDSecure = null,
        public ?bool $createRegistration = null,
        public ?string $registrationId = null,
        public ?string $shopperResultUrl = null,
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
            'shopperResultUrl' => $this->shopperResultUrl,
            'merchantTransactionId' => $this->merchantTransactionId,
            'createRegistration' => $this->createRegistration,
            'registrationId' => $this->registrationId,
        ], fn ($value) => $value !== null);
    }
}
