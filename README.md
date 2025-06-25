# Laravel HyperPay Integration

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ahmadchebbo/laravel-hyperpay.svg?style=flat-square)](https://packagist.org/packages/ahmadchebbo/laravel-hyperpay)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/ahmadchebbo/laravel-hyperpay/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/ahmadchebbo/laravel-hyperpay/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/ahmadchebbo/laravel-hyperpay/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/ahmadchebbo/laravel-hyperpay/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/ahmadchebbo/laravel-hyperpay.svg?style=flat-square)](https://packagist.org/packages/ahmadchebbo/laravel-hyperpay)

A comprehensive Laravel package for integrating HyperPay payment gateway. This package provides a seamless integration with HyperPay's payment processing services, supporting multiple payment brands (VISA, MasterCard, MADA, Apple Pay, STC Pay) with webhook handling, payment status checking, and comprehensive error handling.

## Features

- 🚀 **Easy Integration**: Simple setup and configuration
- 💳 **Multiple Payment Brands**: Support for VISA, MasterCard, MADA, Apple Pay, and STC Pay
- 🔄 **Webhook Handling**: Automatic webhook processing and verification
- 📊 **Payment Status Tracking**: Real-time payment status checking
- 🎯 **Event-Driven**: Laravel events for payment success, failure, and pending states
- 🛡️ **Security**: Built-in webhook verification and error handling
- 📝 **Logging**: Comprehensive logging for debugging and monitoring
- 🎨 **Customizable Views**: Publishable Blade templates
- 🗄️ **Database Integration**: Migration support for payment tracking
- 🔄 **Refund & Capture**: Full support for refunds and payment captures
- 🌍 **Multi-Environment**: Test and live environment support
- ⚡ **Performance**: Optimized for high-performance applications

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
  - [Quick Installation](#quick-installation)
  - [Manual Installation](#manual-installation)
- [Configuration](#configuration)
  - [Environment Variables](#environment-variables)
  - [Configuration File](#configuration-file)
- [Usage](#usage)
  - [Basic Usage](#basic-usage)
    - [Using the Facade](#using-the-facade)
    - [Using Dependency Injection](#using-dependency-injection)
  - [Advanced Usage](#advanced-usage)
    - [Payment Status Checking](#payment-status-checking)
    - [Refund Processing](#refund-processing)
    - [Payment Capture](#payment-capture)
    - [Webhook Handling](#webhook-handling)
    - [Event Handling](#event-handling)
  - [Supported Payment Brands](#supported-payment-brands)
- [API Reference](#api-reference)
  - [Facade Methods](#facade-methods)
  - [DTOs](#dtos)
    - [CheckoutRequest](#checkoutrequest)
    - [PaymentRequest](#paymentrequest)
  - [Events](#events)
- [Card Tokenization](#card-tokenization)
  - [Features](#features-1)
  - [Database Models](#database-models)
    - [Payment Model](#payment-model)
    - [CreditCard Model](#creditcard-model)
  - [User Model Integration](#user-model-integration)
  - [Card Tokenization Usage](#card-tokenization-usage)
    - [One-Time Payment with Tokenization](#one-time-payment-with-tokenization)
    - [Recurring Payment with Saved Card](#recurring-payment-with-saved-card)
  - [Credit Card Management API](#credit-card-management-api)
  - [Credit Card Management Methods](#credit-card-management-methods)
- [File Structure](#file-structure)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)
- [Support](#support)
- [Changelog](#changelog)
- [FAQ / Troubleshooting](#faq--troubleshooting)
- [Security Best Practices](#security-best-practices)

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- Composer

## Installation

You can install the package via composer:

```bash
composer require ahmad-chebbo/laravel-hyperpay
```

### Quick Installation

Run the installation command to set up the package:

```bash
php artisan hyperpay:install --env
```

This command will:
- Publish the configuration file
- Publish migrations
- Publish views
- Set up environment variables in your `.env` file

### Manual Installation

If you prefer manual installation, follow these steps:

1. **Publish the configuration file:**
```bash
php artisan vendor:publish --provider="AhmadChebbo\LaravelHyperpay\HyperPayServiceProvider" --tag="hyperpay-config"
```

2. **Publish migrations:**
```bash
php artisan vendor:publish --provider="AhmadChebbo\LaravelHyperpay\HyperPayServiceProvider" --tag="hyperpay-migrations"
```

3. **Publish views (optional):**
```bash
php artisan vendor:publish --provider="AhmadChebbo\LaravelHyperpay\HyperPayServiceProvider" --tag="hyperpay-views"
```

4. **Run migrations:**
```bash
php artisan migrate
```

## Configuration

### Environment Variables

Add the following variables to your `.env` file:

```env
# Environment (test or live)
HYPERPAY_ENVIRONMENT=test

# Test Environment Configuration
HYPERPAY_TEST_URL=https://eu-test.oppwa.com
HYPERPAY_TEST_TOKEN=your_test_token
HYPERPAY_TEST_WEBHOOK_KEY=your_test_webhook_key
HYPERPAY_TEST_VISA_ENTITY_ID=your_test_visa_entity_id
HYPERPAY_TEST_MASTER_ENTITY_ID=your_test_master_entity_id
HYPERPAY_TEST_MADA_ENTITY_ID=your_test_mada_entity_id
HYPERPAY_TEST_APPLEPAY_ENTITY_ID=your_test_applepay_entity_id
HYPERPAY_TEST_STCPAY_ENTITY_ID=your_test_stcpay_entity_id

# Live Environment Configuration
HYPERPAY_LIVE_URL=https://oppwa.com
HYPERPAY_LIVE_TOKEN=your_live_token
HYPERPAY_LIVE_WEBHOOK_KEY=your_live_webhook_key
HYPERPAY_LIVE_VISA_ENTITY_ID=your_live_visa_entity_id
HYPERPAY_LIVE_MASTER_ENTITY_ID=your_live_master_entity_id
HYPERPAY_LIVE_MADA_ENTITY_ID=your_live_mada_entity_id
HYPERPAY_LIVE_APPLEPAY_ENTITY_ID=your_live_applepay_entity_id
HYPERPAY_LIVE_STCPAY_ENTITY_ID=your_live_stcpay_entity_id

# General Settings
HYPERPAY_CURRENCY=SAR
HYPERPAY_PAYMENT_TYPE=DB

# Webhook Settings
HYPERPAY_WEBHOOK_ENABLED=true
HYPERPAY_WEBHOOK_URL=https://your-domain.com/hyperpay/webhook
HYPERPAY_WEBHOOK_VERIFY_SIGNATURE=true

# Logging
HYPERPAY_LOGGING_ENABLED=true
HYPERPAY_LOGGING_CHANNEL=single
HYPERPAY_LOGGING_LEVEL=info

# Customization
HYPERPAY_AUTO_REDIRECT=true
HYPERPAY_RETRY_ATTEMPTS=3
HYPERPAY_TIMEOUT=30
HYPERPAY_RESULT_URL=https://your-domain.com/payment/result
HYPERPAY_CANCEL_URL=https://your-domain.com/payment/cancel
HYPERPAY_ERROR_URL=https://your-domain.com/payment/error

# Risk Management
HYPERPAY_RISK_MANAGEMENT_ENABLED=true
HYPERPAY_MAX_AMOUNT=50000
HYPERPAY_MIN_AMOUNT=1
```

### Configuration File

The configuration file is located at `config/hyperpay.php`. You can customize various settings including:

- Environment configuration (test/live)
- Payment brands and entity IDs
- Webhook settings
- Logging configuration
- Risk management rules
- Customization options

## Usage

### Basic Usage

#### Using the Facade

```php
use AhmadChebbo\LaravelHyperpay\Facades\HyperPay;
use AhmadChebbo\LaravelHyperpay\DTOs\CheckoutRequest;
use AhmadChebbo\LaravelHyperpay\DTOs\CustomerData;
use AhmadChebbo\LaravelHyperpay\DTOs\BillingData;

// Create a checkout session
$checkoutRequest = new CheckoutRequest(
    amount: 100.00,
    brand: 'VISA',
    currency: 'SAR',
    merchantTransactionId: 'order_123',
    customer: new CustomerData(
        givenName: 'John',
        surname: 'Doe',
        email: 'john.doe@example.com',
        phone: '+966501234567'
    ),
    billing: new BillingData(
        street1: 'King Fahd Road',
        city: 'Riyadh',
        state: 'Riyadh',
        postcode: '12345',
        country: 'SA'
    )
);

$response = HyperPay::createCheckout($checkoutRequest);

// Redirect to payment page
return redirect($response->redirectUrl);
```

#### Using Dependency Injection

```php
use AhmadChebbo\LaravelHyperpay\Services\HyperPayService;

class PaymentController extends Controller
{
    public function __construct(
        private HyperPayService $hyperPayService
    ) {}

    public function processPayment(Request $request)
    {
        $paymentRequest = new PaymentRequest(
            amount: 100.00,
            brand: 'VISA',
            cardNumber: '4111111111111111',
            cardHolder: 'John Doe',
            expiryMonth: 12,
            expiryYear: 2025,
            cvv: '123'
        );

        $response = $this->hyperPayService->processPayment($paymentRequest);

        if ($response->isSuccessful()) {
            return response()->json(['success' => true, 'payment_id' => $response->id]);
        }

        return response()->json(['success' => false, 'error' => $response->result->description]);
    }
}
```

### Advanced Usage

#### Payment Status Checking

```php
// Check payment status
$status = HyperPay::getPaymentStatus('payment_id_here', 'VISA');

if ($status->isSuccessful()) {
    // Payment completed successfully
    $amount = $status->amount;
    $currency = $status->currency;
    $transactionId = $status->merchantTransactionId;
}
```

#### Refund Processing

```php
// Process refund
$refund = HyperPay::processRefund(
    paymentId: 'payment_id_here',
    amount: 50.00,
    brand: 'VISA',
    reason: 'Customer requested partial refund'
);

if ($refund->isSuccessful()) {
    // Refund processed successfully
}
```

#### Payment Capture (for pre-authorized payments)

```php
// Capture pre-authorized payment
$capture = HyperPay::capturePayment(
    paymentId: 'payment_id_here',
    amount: 100.00,
    brand: 'VISA'
);

if ($capture->isSuccessful()) {
    // Payment captured successfully
}
```

#### Webhook Handling

The package automatically handles webhooks. Add the webhook route to your `routes/web.php`:

```php
Route::post('/hyperpay/webhook', [WebhookController::class, 'handle'])->name('hyperpay.webhook');
```

#### Event Handling

The package dispatches various events that you can listen to:

```php
// In your EventServiceProvider
protected $listen = [
    'AhmadChebbo\LaravelHyperpay\Events\PaymentSuccessful' => [
        'App\Listeners\HandleSuccessfulPayment',
    ],
    'AhmadChebbo\LaravelHyperpay\Events\PaymentFailed' => [
        'App\Listeners\HandleFailedPayment',
    ],
    'AhmadChebbo\LaravelHyperpay\Events\PaymentPending' => [
        'App\Listeners\HandlePendingPayment',
    ],
];
```

### Supported Payment Brands

- **VISA**: Visa credit and debit cards
- **MASTER**: MasterCard credit and debit cards
- **MADA**: Saudi MADA cards
- **APPLEPAY**: Apple Pay
- **STCPAY**: STC Pay

## API Reference

### Facade Methods

| Method | Description | Parameters | Returns |
|--------|-------------|------------|---------|
| `createCheckout()` | Create a hosted checkout session | `CheckoutRequest` | `CheckoutResponse` |
| `processPayment()` | Process direct payment | `PaymentRequest` | `PaymentResponse` |
| `getPaymentStatus()` | Get payment status | `string $paymentId, ?string $brand` | `PaymentResponse` |
| `processRefund()` | Process refund | `string $paymentId, float $amount, string $brand, ?string $reason` | `PaymentResponse` |
| `capturePayment()` | Capture pre-authorized payment | `string $paymentId, float $amount, string $brand` | `PaymentResponse` |
| `reversePayment()` | Reverse payment | `string $paymentId, string $brand` | `PaymentResponse` |
| `getSupportedBrands()` | Get supported payment brands | - | `array` |
| `isBrandSupported()` | Check if brand is supported | `string $brand` | `bool` |

### DTOs

#### CheckoutRequest
```php
new CheckoutRequest(
    amount: float,
    brand: string,
    currency?: string,
    paymentType?: string,
    merchantTransactionId?: string,
    customer?: CustomerData,
    billing?: BillingData,
    shipping?: ShippingData,
    customParameters?: array,
    riskParameters?: array
)
```

#### PaymentRequest
```php
new PaymentRequest(
    amount: float,
    brand: string,
    cardNumber: string,
    cardHolder: string,
    expiryMonth: int,
    expiryYear: int,
    cvv: string,
    currency?: string,
    paymentType?: string,
    merchantTransactionId?: string,
    customer?: CustomerData,
    billing?: BillingData,
    shipping?: ShippingData,
    threeDSecure?: array
)
```

### Events

| Event | Description | Properties |
|-------|-------------|------------|
| `PaymentSuccessful` | Fired when payment is successful | `paymentId`, `amount`, `currency`, `brand` |
| `PaymentFailed` | Fired when payment fails | `paymentId`, `error`, `brand` |
| `PaymentPending` | Fired when payment is pending | `paymentId`, `brand` |
| `PaymentStatusChanged` | Fired when payment status changes | `paymentId`, `oldStatus`, `newStatus` |
| `RefundProcessed` | Fired when refund is processed | `paymentId`, `amount`, `brand` |
| `ChargebackReceived` | Fired when chargeback is received | `paymentId`, `amount`, `brand` |

## Card Tokenization

The package supports card tokenization for secure recurring payments and one-click checkout.

### Features

- **Secure Storage**: Card details are never stored in your database
- **Token Management**: Store and manage card tokens with morphable relationships
- **Recurring Payments**: Use saved cards for future transactions
- **Default Cards**: Set default payment methods for users
- **Expiry Validation**: Automatic expiry checking and management

### Database Models

#### Payment Model
```php
use AhmadChebbo\LaravelHyperpay\Models\Payment;

// Create payment record
Payment::create([
    'transaction_id' => 'payment_123',
    'amount' => 100.00,
    'currency' => 'SAR',
    'brand' => 'VISA',
    'status' => 'successful',
    'card_token' => 'registration_id_from_hyperpay',
    'payable_type' => User::class,
    'payable_id' => $user->id,
]);
```

#### CreditCard Model
```php
use AhmadChebbo\LaravelHyperpay\Models\CreditCard;

// Credit card with morphable relationships
$creditCard = CreditCard::create([
    'registration_id' => 'hyperpay_registration_id',
    'card_type' => 'VISA',
    'last_four_digits' => '1234',
    'card_holder_name' => 'John Doe',
    'expiry_month' => '12',
    'expiry_year' => '2025',
    'is_default' => true,
    'cardable_type' => User::class,
    'cardable_id' => $user->id,
]);
```

### User Model Integration

Add the traits to your User model:

```php
use AhmadChebbo\LaravelHyperpay\Traits\HasPayments;
use AhmadChebbo\LaravelHyperpay\Traits\HasCreditCards;

class User extends Authenticatable
{
    use HasPayments, HasCreditCards;
    
    // Now you have access to:
    // $user->payments()
    // $user->creditCards()
    // $user->defaultCreditCard()
    // $user->saveCreditCardFromResponse()
}
```

### Card Tokenization Usage

#### One-Time Payment with Tokenization
```php
use AhmadChebbo\LaravelHyperpay\Facades\HyperPay;
use AhmadChebbo\LaravelHyperpay\DTOs\PaymentRequest;

// Process payment and request tokenization
$paymentRequest = new PaymentRequest(
    amount: 100.00,
    brand: 'VISA',
    cardNumber: '4111111111111111',
    cardHolder: 'John Doe',
    expiryMonth: 12,
    expiryYear: 2025,
    cvv: '123',
    createRegistration: true, // Request tokenization
);

$response = HyperPay::processPayment($paymentRequest);

if ($response->isSuccessful() && isset($response->card_token)) {
    // Save card token to user
    $user->saveCreditCardFromResponse($response->toArray(), [
        'last_four_digits' => '1111',
        'card_holder_name' => 'John Doe',
        'expiry_month' => '12',
        'expiry_year' => '2025',
        'is_default' => true,
    ]);
}
```

#### Recurring Payment with Saved Card
```php
// Use saved card for payment
$paymentRequest = new PaymentRequest(
    amount: 50.00,
    brand: 'VISA',
    registrationId: $user->defaultCreditCard()->registration_id,
);

$response = HyperPay::processPayment($paymentRequest);
```

### Credit Card Management API

#### Get User's Cards
```bash
GET /api/credit-cards
```

#### Add New Card (Tokenize)
```bash
POST /api/credit-cards
{
    "amount": 1.00,
    "brand": "VISA",
    "card_number": "4111111111111111",
    "card_holder": "John Doe",
    "expiry_month": 12,
    "expiry_year": 2025,
    "cvv": "123",
    "is_default": true
}
```

#### Pay with Saved Card
```bash
POST /api/credit-cards/{registrationId}/pay
{
    "amount": 100.00,
    "currency": "SAR"
}
```

#### Set Card as Default
```bash
POST /api/credit-cards/{registrationId}/default
```

#### Remove Card
```bash
DELETE /api/credit-cards/{registrationId}
```

### Credit Card Management Methods

```php
// Get user's cards
$user->creditCards();           // All cards
$user->activeCreditCards();     // Active cards only
$user->defaultCreditCard();     // Default card
$user->validCreditCards();      // Non-expired cards

// Card management
$user->saveCreditCardFromResponse($response, $cardInfo);
$user->getCreditCardByRegistrationId($registrationId);
$user->removeCreditCard($registrationId);
$user->hasCreditCards();
$user->hasCreditCard($registrationId);

// Card properties
$card->masked_card_number;      // "**** **** **** 1234"
$card->formatted_expiry;        // "12/2025"
$card->is_expired;              // true/false
$card->setAsDefault();          // Set as default
$card->deactivate();            // Deactivate card
$card->reactivate();            // Reactivate card
```

## File Structure

```
laravel-hyperpay-gateway/
├── CHANGELOG.md                    # Package changelog
├── composer.json                   # Composer configuration
├── config/
│   └── hyperpay.php               # Package configuration file
├── database/
│   └── migrations/
│       ├── create_payments_table.php      # Payments table migration
│       └── create_credit_cards_table.php  # Credit cards table migration
├── LICENSE.md                      # MIT License
├── phpstan.neon.dist              # PHPStan configuration
├── phpunit.xml.dist               # PHPUnit configuration
├── README.md                       # This file
├── resources/
│   └── views/                     # Blade view templates
│       └── payment/               # Payment result views
│           ├── success.blade.php  # Success page
│           ├── failed.blade.php   # Failed page
│           ├── pending.blade.php  # Pending page
│           └── cancelled.blade.php # Cancelled page
├── routes/
│   └── hyperpay.php               # Package routes
├── src/
│   ├── Commands/                  # Artisan commands
│   │   ├── HyperPayInstallCommand.php    # Installation command
│   │   └── HyperPayStatusCommand.php     # Status check command
│   ├── DTOs/                      # Data Transfer Objects
│   │   ├── BillingData.php        # Billing information DTO
│   │   ├── CheckoutRequest.php    # Checkout request DTO
│   │   ├── CheckoutResponse.php   # Checkout response DTO
│   │   ├── CustomerData.php       # Customer information DTO
│   │   ├── PaymentRequest.php     # Payment request DTO
│   │   ├── PaymentResponse.php    # Payment response DTO
│   │   └── ShippingData.php       # Shipping information DTO
│   ├── Events/                    # Laravel events
│   │   ├── ChargebackReceived.php # Chargeback event
│   │   ├── PaymentFailed.php      # Payment failure event
│   │   ├── PaymentPending.php     # Payment pending event
│   │   ├── PaymentStatusChanged.php # Payment status change event
│   │   ├── PaymentSuccessful.php  # Payment success event
│   │   └── RefundProcessed.php    # Refund processed event
│   ├── Exceptions/                # Custom exceptions
│   │   ├── HyperPayException.php  # Base exception
│   │   ├── InvalidAmountException.php # Invalid amount exception
│   │   ├── InvalidBrandException.php  # Invalid brand exception
│   │   └── WebhookVerificationException.php # Webhook verification exception
│   ├── Facades/                   # Laravel facades
│   │   └── HyperPay.php           # Main HyperPay facade
│   ├── Http/
│   │   └── Controllers/           # HTTP controllers
│   │       ├── PaymentController.php    # Payment processing controller
│   │       ├── WebhookController.php    # Webhook handling controller
│   │       └── CreditCardController.php # Credit card management controller
│   ├── Listeners/                 # Event listeners
│   │   └── HandleSuccessfulPayment.php  # Example payment success listener
│   ├── Models/                    # Database models
│   │   ├── Payment.php            # Payment model
│   │   └── CreditCard.php         # Credit card model
│   ├── Traits/                    # Eloquent traits
│   │   ├── HasPayments.php        # Payments trait
│   │   └── HasCreditCards.php     # Credit cards trait
│   ├── Hyperpay.php               # Main service class
│   ├── HyperpayServiceProvider.php # Service provider
│   └── Services/                  # Service classes
│       ├── HyperPayResultCodeService.php # Result code handling service
│       ├── HyperPayService.php    # Main HyperPay service
│       └── WebhookService.php     # Webhook processing service
└── tests/                         # Test files
    ├── Feature/
    │   └── PaymentFlowTest.php    # Payment flow feature tests
    ├── Unit/
    │   └── HyperPayServiceTest.php # Service unit tests
    ├── Pest.php                   # Pest configuration
    └── TestCase.php               # Base test case
```

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

Run static analysis:

```bash
composer analyse
```

Format code:

```bash
composer format
```

## Security

If you discover any security-related issues, please email ahmad.m.shebbo@gmail.com instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Credits

- [Ahmad Chebbo](https://github.com/ahmad-chebbo)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

For support, please email ahmad.m.shebbo@gmail.com or create an issue on GitHub.

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information about what has changed recently.

## FAQ / Troubleshooting

**Q: How do I enable card tokenization?**
A: Set `createRegistration: true` in your PaymentRequest or CheckoutRequest DTO.

**Q: How do I use a saved card for payment?**
A: Use the `registrationId` from the saved CreditCard model in your PaymentRequest.

**Q: How do I customize the payment model?**
A: Set `HYPERPAY_PAYMENT_MODEL` in your `.env` file to your custom model class.

**Q: Webhook is not firing, what should I check?**
A: Ensure your endpoint is publicly accessible, `HYPERPAY_WEBHOOK_ENABLED` is set to `true`, and check your server logs for errors.

**Q: How do I enable debug logging?**
A: Set `HYPERPAY_LOGGING_LEVEL=debug` in your `.env` file.

**Q: How do I handle expired cards?**
A: Use the `is_expired` attribute or `notExpired()` scope on the CreditCard model.

## Security Best Practices

- **Never store raw card data** - Only store the `registrationId` from HyperPay
- **Use HTTPS** for all payment endpoints
- **Validate webhook signatures** to ensure requests are from HyperPay
- **Implement proper authentication** for credit card management
- **Use environment-specific credentials** (test vs live)
- **Log payment activities** for audit trails
- **Implement rate limiting** on payment endpoints
- **Validate all inputs** before processing payments
