<?php

namespace AhmadShebbo\LaravelHyperpay\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \YourVendor\LaravelHyperPay\DTOs\CheckoutResponse createCheckout(\YourVendor\LaravelHyperPay\DTOs\CheckoutRequest $request)
 * @method static \YourVendor\LaravelHyperPay\DTOs\PaymentResponse processPayment(\YourVendor\LaravelHyperPay\DTOs\PaymentRequest $request)
 * @method static \YourVendor\LaravelHyperPay\DTOs\PaymentResponse getPaymentStatus(string $paymentId, string $brand = null)
 * @method static \YourVendor\LaravelHyperPay\DTOs\PaymentResponse processRefund(string $paymentId, float $amount, string $brand, string $reason = null)
 * @method static \YourVendor\LaravelHyperPay\DTOs\PaymentResponse capturePayment(string $paymentId, float $amount, string $brand)
 * @method static \YourVendor\LaravelHyperPay\DTOs\PaymentResponse reversePayment(string $paymentId, string $brand)
 * @method static array getSupportedBrands()
 * @method static bool isBrandSupported(string $brand)
 */
class HyperPay extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'hyperpay';
    }
}
