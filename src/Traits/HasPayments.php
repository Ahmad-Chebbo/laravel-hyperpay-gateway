<?php

declare(strict_types=1);

namespace AhmadShebbo\LaravelHyperpay\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasPayments
{
    public function payments(): MorphMany
    {
        return $this->morphMany(\AhmadShebbo\LaravelHyperpay\Models\Payment::getModelClass(), 'payable');
    }

    public function successfulPayments(): MorphMany
    {
        return $this->payments()->where('status', 'successful');
    }

    public function failedPayments(): MorphMany
    {
        return $this->payments()->where('status', 'failed');
    }

    public function pendingPayments(): MorphMany
    {
        return $this->payments()->where('status', 'pending');
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->successfulPayments()->sum('amount');
    }

    public function getLastPaymentAttribute(): ?\AhmadShebbo\LaravelHyperpay\Models\Payment
    {
        return $this->payments()->latest()->first();
    }
}
