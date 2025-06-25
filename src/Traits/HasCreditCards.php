<?php

declare(strict_types=1);

namespace AhmadChebbo\LaravelHyperpay\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCreditCards
{
    /**
     * Get all credit cards for this model
     */
    public function creditCards(): MorphMany
    {
        return $this->morphMany(\AhmadChebbo\LaravelHyperpay\Models\CreditCard::class, 'cardable');
    }

    /**
     * Get active credit cards
     */
    public function activeCreditCards(): MorphMany
    {
        return $this->creditCards()->active();
    }

    /**
     * Get default credit card
     */
    public function defaultCreditCard(): ?\AhmadChebbo\LaravelHyperpay\Models\CreditCard
    {
        return $this->creditCards()->default()->first();
    }

    /**
     * Get non-expired credit cards
     */
    public function validCreditCards(): MorphMany
    {
        return $this->creditCards()->active()->notExpired();
    }

    /**
     * Add a new credit card
     */
    public function addCreditCard(array $cardData): \AhmadChebbo\LaravelHyperpay\Models\CreditCard
    {
        return $this->creditCards()->create($cardData);
    }

    /**
     * Save credit card from HyperPay response
     */
    public function saveCreditCardFromResponse(array $response, array $cardInfo = []): ?\AhmadChebbo\LaravelHyperpay\Models\CreditCard
    {
        if (! isset($response['registrationId'])) {
            return null;
        }

        $cardData = [
            'registration_id' => $response['registrationId'],
            'card_type' => $response['paymentBrand'] ?? $cardInfo['card_type'] ?? 'UNKNOWN',
            'last_four_digits' => $cardInfo['last_four_digits'] ?? '****',
            'card_holder_name' => $cardInfo['card_holder_name'] ?? 'Unknown',
            'expiry_month' => $cardInfo['expiry_month'] ?? '**',
            'expiry_year' => $cardInfo['expiry_year'] ?? '****',
            'is_default' => $cardInfo['is_default'] ?? false,
            'is_active' => true,
            'metadata' => array_merge($cardInfo, ['response_data' => $response]),
        ];

        $creditCard = $this->addCreditCard($cardData);

        // Set as default if requested
        if ($cardData['is_default']) {
            $creditCard->setAsDefault();
        }

        return $creditCard;
    }

    /**
     * Get credit card by registration ID
     */
    public function getCreditCardByRegistrationId(string $registrationId): ?\AhmadChebbo\LaravelHyperpay\Models\CreditCard
    {
        return $this->creditCards()
            ->where('registration_id', $registrationId)
            ->active()
            ->first();
    }

    /**
     * Remove a credit card
     */
    public function removeCreditCard(string $registrationId): bool
    {
        $card = $this->getCreditCardByRegistrationId($registrationId);

        if ($card) {
            return $card->delete();
        }

        return false;
    }

    /**
     * Check if user has any credit cards
     */
    public function hasCreditCards(): bool
    {
        return $this->creditCards()->active()->exists();
    }

    /**
     * Check if user has a specific credit card
     */
    public function hasCreditCard(string $registrationId): bool
    {
        return $this->getCreditCardByRegistrationId($registrationId) !== null;
    }

    /**
     * Get credit cards count
     */
    public function getCreditCardsCountAttribute(): int
    {
        return $this->creditCards()->active()->count();
    }

    /**
     * Get valid credit cards count
     */
    public function getValidCreditCardsCountAttribute(): int
    {
        return $this->validCreditCards()->count();
    }
}
