<?php

namespace AhmadShebbo\LaravelHyperpay\DTOs;

/**
 * Checkout Response DTO
 */
class CheckoutResponse
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

    public function getCheckoutUrl(): ?string
    {
        return $this->data['links']['self']['href'] ?? null;
    }

    public function getPaymentPageUrl(): ?string
    {
        return $this->data['links']['payment']['href'] ?? null;
    }

    public function getTimestamp(): ?string
    {
        return $this->data['timestamp'] ?? null;
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
