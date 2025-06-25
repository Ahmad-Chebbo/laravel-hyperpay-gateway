<?php

declare(strict_types=1);

namespace Ahmad-Chebbo\LaravelHyperpay\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CreditCard extends Model
{
    protected $table = 'credit_cards';

    protected $fillable = [
        'registration_id',
        'card_type',
        'last_four_digits',
        'card_holder_name',
        'expiry_month',
        'expiry_year',
        'is_default',
        'is_active',
        'metadata',
        'cardable_type',
        'cardable_id',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the cardable model (User, Customer, etc.)
     */
    public function cardable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the masked card number for display
     */
    public function getMaskedCardNumberAttribute(): string
    {
        return '**** **** **** '.$this->last_four_digits;
    }

    /**
     * Get the formatted expiry date
     */
    public function getFormattedExpiryAttribute(): string
    {
        return $this->expiry_month.'/'.$this->expiry_year;
    }

    /**
     * Check if card is expired
     */
    public function getIsExpiredAttribute(): bool
    {
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');

        if ((int) $this->expiry_year < $currentYear) {
            return true;
        }

        if ((int) $this->expiry_year === $currentYear && (int) $this->expiry_month < $currentMonth) {
            return true;
        }

        return false;
    }

    /**
     * Scope to get only active cards
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only default cards
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to get only non-expired cards
     */
    public function scopeNotExpired($query)
    {
        $currentYear = date('Y');
        $currentMonth = date('m');

        return $query->where(function ($q) use ($currentYear, $currentMonth) {
            $q->where('expiry_year', '>', $currentYear)
                ->orWhere(function ($subQ) use ($currentYear, $currentMonth) {
                    $subQ->where('expiry_year', $currentYear)
                        ->where('expiry_month', '>=', $currentMonth);
                });
        });
    }

    /**
     * Set this card as default and unset others
     */
    public function setAsDefault(): void
    {
        // Unset other default cards for this user
        static::where('cardable_type', $this->cardable_type)
            ->where('cardable_id', $this->cardable_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Set this card as default
        $this->update(['is_default' => true]);
    }

    /**
     * Deactivate the card
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Reactivate the card
     */
    public function reactivate(): void
    {
        $this->update(['is_active' => true]);
    }
}
