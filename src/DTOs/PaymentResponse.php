<?php

namespace AhmadChebbo\LaravelHyperpay\DTOs;

/**
 * Payment Response DTO
 */
class PaymentResponse
{
    public function __construct(
        protected array $data
    ) {}

    public function getId(): ?string
    {
        return $this->data['id'] ?? null;
    }

    public function getResultCode(): ?string
    {
        return $this->data['result']['code'] ?? null;
    }

    public function getResultDescription(): ?string
    {
        return $this->data['result']['description'] ?? null;
    }

    public function isSuccessful(): bool
    {
        $code = $this->getResultCode();
        return $code && app('hyperpay.result')->isSuccessful($code);
    }

    public function needsReview(): bool
    {
        $code = $this->getResultCode();
        return $code && app('hyperpay.result')->isSuccessfulButNeedsReview($code);
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

    public function getAmount(): ?string
    {
        return $this->data['amount'] ?? null;
    }

    public function getCurrency(): ?string
    {
        return $this->data['currency'] ?? null;
    }

    public function getPaymentBrand(): ?string
    {
        return $this->data['paymentBrand'] ?? null;
    }

    public function getPaymentType(): ?string
    {
        return $this->data['paymentType'] ?? null;
    }

    public function getMerchantTransactionId(): ?string
    {
        return $this->data['merchantTransactionId'] ?? null;
    }

    public function getTimestamp(): ?string
    {
        return $this->data['timestamp'] ?? null;
    }

    public function getCard(): ?array
    {
        return $this->data['card'] ?? null;
    }

    public function getCustomer(): ?array
    {
        return $this->data['customer'] ?? null;
    }

    public function getBilling(): ?array
    {
        return $this->data['billing'] ?? null;
    }

    public function getShipping(): ?array
    {
        return $this->data['shipping'] ?? null;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->data['redirect']['url'] ?? null;
    }

    public function get3DSecureInfo(): ?array
    {
        return $this->data['threeDSecure'] ?? null;
    }

    public function getRisk(): ?array
    {
        return $this->data['risk'] ?? null;
    }

    public function getCustomParameters(): ?array
    {
        return $this->data['customParameters'] ?? null;
    }

    public function getRawData(): array
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}