<?php

namespace AhmadChebbo\LaravelHyperpay\DTOs;

/**
 * Billing Data DTO
 */
class BillingData
{
    public function __construct(
        public ?string $street1 = null,
        public ?string $street2 = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $postcode = null,
        public ?string $country = null,
    ) {}

    public function toArray(): array
    {
        $data = [];

        if ($this->street1) $data['billing.street1'] = $this->street1;
        if ($this->street2) $data['billing.street2'] = $this->street2;
        if ($this->city) $data['billing.city'] = $this->city;
        if ($this->state) $data['billing.state'] = $this->state;
        if ($this->postcode) $data['billing.postcode'] = $this->postcode;
        if ($this->country) $data['billing.country'] = $this->country;

        return $data;
    }
}