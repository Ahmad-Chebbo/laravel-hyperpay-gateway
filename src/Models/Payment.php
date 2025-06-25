<?php

declare(strict_types=1);

namespace Ahmad-Chebbo\LaravelHyperpay\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'transaction_id',
        'amount',
        'currency',
        'brand',
        'status',
        'response',
        'card_token',
        'payable_type',
        'payable_id',
    ];

    protected $casts = [
        'amount' => 'float',
        'response' => 'array',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the configured payment model class
     */
    public static function getModelClass(): string
    {
        return config('hyperpay.models.payment', self::class);
    }

    /**
     * Create a new instance of the configured payment model
     */
    public static function createModel(): self
    {
        $class = self::getModelClass();

        return new $class;
    }
}
