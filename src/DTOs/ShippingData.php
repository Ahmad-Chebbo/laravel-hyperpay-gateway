<?php

namespace AhmadShebbo\LaravelHyperpay\DTOs;

/**
 * Shipping Data DTO
 */
class ShippingData
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

        if ($this->street1) {
            $data['shipping.street1'] = $this->street1;
        }
        if ($this->street2) {
            $data['shipping.street2'] = $this->street2;
        }
        if ($this->city) {
            $data['shipping.city'] = $this->city;
        }
        if ($this->state) {
            $data['shipping.state'] = $this->state;
        }
        if ($this->postcode) {
            $data['shipping.postcode'] = $this->postcode;
        }
        if ($this->country) {
            $data['shipping.country'] = $this->country;
        }

        return $data;
    }
}
