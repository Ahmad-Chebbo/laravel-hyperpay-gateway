<?php

namespace Ahmad-Chebbo\LaravelHyperpay\DTOs;

/**
 * Customer Data DTO
 */
class CustomerData
{
    public function __construct(
        public ?string $givenName = null,
        public ?string $surname = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $ip = null,
        public ?string $merchantCustomerId = null,
        public ?string $identificationDocId = null,
        public ?string $identificationType = null,
    ) {}

    public function toArray(): array
    {
        $data = [];

        if ($this->givenName) {
            $data['customer.givenName'] = $this->givenName;
        }
        if ($this->surname) {
            $data['customer.surname'] = $this->surname;
        }
        if ($this->email) {
            $data['customer.email'] = $this->email;
        }
        if ($this->phone) {
            $data['customer.phone'] = $this->phone;
        }
        if ($this->ip) {
            $data['customer.ip'] = $this->ip;
        }
        if ($this->merchantCustomerId) {
            $data['customer.merchantCustomerId'] = $this->merchantCustomerId;
        }
        if ($this->identificationDocId) {
            $data['customer.identificationDocId'] = $this->identificationDocId;
        }
        if ($this->identificationType) {
            $data['customer.identificationType'] = $this->identificationType;
        }

        return $data;
    }
}
