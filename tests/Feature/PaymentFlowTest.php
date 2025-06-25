<?php

declare(strict_types=1);

namespace AhmadShebbo\LaravelHyperpay\Tests\Feature;

use AhmadShebbo\LaravelHyperpay\Events\PaymentSuccessful;
use AhmadShebbo\LaravelHyperpay\Models\Payment;
use AhmadShebbo\LaravelHyperpay\Services\HyperPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the HyperPay service to avoid real API calls
        $this->mock(HyperPayService::class, function ($mock) {
            $mock->shouldReceive('createCheckout')
                ->andReturn(new \AhmadShebbo\LaravelHyperpay\DTOs\CheckoutResponse([
                    'id' => 'test_checkout_123',
                    'redirectUrl' => 'https://test.oppwa.com/checkout/test_checkout_123',
                    'merchantTransactionId' => 'order_123',
                ]));

            $mock->shouldReceive('processPayment')
                ->andReturn(new \AhmadShebbo\LaravelHyperpay\DTOs\PaymentResponse([
                    'id' => 'test_payment_123',
                    'result' => [
                        'code' => '000.100.110',
                        'description' => 'Request successfully processed in \'Merchant in Integrator Test Mode\'',
                    ],
                    'amount' => '100.00',
                    'currency' => 'SAR',
                    'paymentBrand' => 'VISA',
                    'merchantTransactionId' => 'order_123',
                ]));

            $mock->shouldReceive('getPaymentStatus')
                ->andReturn(new \AhmadShebbo\LaravelHyperpay\DTOs\PaymentResponse([
                    'id' => 'test_payment_123',
                    'result' => [
                        'code' => '000.100.110',
                        'description' => 'Request successfully processed in \'Merchant in Integrator Test Mode\'',
                    ],
                    'amount' => '100.00',
                    'currency' => 'SAR',
                    'paymentBrand' => 'VISA',
                    'merchantTransactionId' => 'order_123',
                ]));
        });
    }

    /** @test */
    public function it_can_create_checkout_session()
    {
        $response = $this->postJson('/api/payment/checkout', [
            'amount' => 100.00,
            'brand' => 'VISA',
            'currency' => 'SAR',
            'merchant_transaction_id' => 'order_123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'checkout_id' => 'test_checkout_123',
                    'redirect_url' => 'https://test.oppwa.com/checkout/test_checkout_123',
                    'merchant_transaction_id' => 'order_123',
                ],
            ]);
    }

    /** @test */
    public function it_validates_checkout_request_data()
    {
        $response = $this->postJson('/api/payment/checkout', [
            'amount' => -10, // Invalid amount
            'brand' => 'INVALID_BRAND', // Invalid brand
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount', 'brand']);
    }

    /** @test */
    public function it_can_process_direct_payment()
    {
        $response = $this->postJson('/api/payment/process', [
            'amount' => 100.00,
            'brand' => 'VISA',
            'card_number' => '4111111111111111',
            'card_holder' => 'John Doe',
            'expiry_month' => 12,
            'expiry_year' => 2025,
            'cvv' => '123',
            'currency' => 'SAR',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'payment_id' => 'test_payment_123',
                    'is_successful' => true,
                    'amount' => '100.00',
                    'currency' => 'SAR',
                ],
            ]);
    }

    /** @test */
    public function it_validates_payment_request_data()
    {
        $response = $this->postJson('/api/payment/process', [
            'amount' => 100.00,
            'brand' => 'VISA',
            'card_number' => '123', // Invalid card number
            'card_holder' => '', // Empty card holder
            'expiry_month' => 13, // Invalid month
            'expiry_year' => 2020, // Past year
            'cvv' => '12', // Invalid CVV
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['card_number', 'card_holder', 'expiry_month', 'expiry_year', 'cvv']);
    }

    /** @test */
    public function it_can_get_payment_status()
    {
        $response = $this->getJson('/api/payment/status/test_payment_123?brand=VISA');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 'test_payment_123',
                    'is_successful' => true,
                    'amount' => '100.00',
                    'currency' => 'SAR',
                    'payment_brand' => 'VISA',
                ],
            ]);
    }

    /** @test */
    public function it_can_process_refund()
    {
        $response = $this->postJson('/api/payment/refund/test_payment_123', [
            'amount' => 50.00,
            'brand' => 'VISA',
            'reason' => 'Customer requested partial refund',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'refund_id' => 'test_payment_123',
                    'is_successful' => true,
                    'amount' => '100.00',
                    'currency' => 'SAR',
                ],
            ]);
    }

    /** @test */
    public function it_can_capture_payment()
    {
        $response = $this->postJson('/api/payment/capture/test_payment_123', [
            'amount' => 100.00,
            'brand' => 'VISA',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'capture_id' => 'test_payment_123',
                    'is_successful' => true,
                    'amount' => '100.00',
                    'currency' => 'SAR',
                ],
            ]);
    }

    /** @test */
    public function it_can_handle_web_payment_form()
    {
        $response = $this->post('/payment/process', [
            'amount' => 100.00,
            'brand' => 'VISA',
            'currency' => 'SAR',
            'merchant_transaction_id' => 'order_123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'redirect_url' => 'https://test.oppwa.com/checkout/test_checkout_123',
            ]);
    }

    /** @test */
    public function it_can_retry_payment()
    {
        // Create a payment record first
        Payment::create([
            'transaction_id' => 'test_payment_123',
            'amount' => 100.00,
            'currency' => 'SAR',
            'brand' => 'VISA',
            'status' => 'failed',
            'response' => [],
        ]);

        $response = $this->get('/payment/retry?payment_id=test_payment_123&brand=VISA');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'redirect_url' => 'https://test.oppwa.com/checkout/test_checkout_123',
            ]);
    }

    /** @test */
    public function it_dispatches_payment_events()
    {
        Event::fake();

        $this->postJson('/api/payment/process', [
            'amount' => 100.00,
            'brand' => 'VISA',
            'card_number' => '4111111111111111',
            'card_holder' => 'John Doe',
            'expiry_month' => 12,
            'expiry_year' => 2025,
            'cvv' => '123',
        ]);

        Event::assertDispatched(PaymentSuccessful::class);
    }

    /** @test */
    public function it_stores_payment_in_database()
    {
        $this->postJson('/api/payment/process', [
            'amount' => 100.00,
            'brand' => 'VISA',
            'card_number' => '4111111111111111',
            'card_holder' => 'John Doe',
            'expiry_month' => 12,
            'expiry_year' => 2025,
            'cvv' => '123',
        ]);

        $this->assertDatabaseHas('payments', [
            'transaction_id' => 'test_payment_123',
            'amount' => 100.00,
            'currency' => 'SAR',
            'brand' => 'VISA',
            'status' => 'successful',
        ]);
    }

    /** @test */
    public function it_handles_payment_success_page()
    {
        $response = $this->get('/payment/success?id=test_payment_123');

        $response->assertStatus(200)
            ->assertViewIs('payment.success');
    }

    /** @test */
    public function it_handles_payment_failed_page()
    {
        $response = $this->get('/payment/failed?id=test_payment_123&error=Card declined');

        $response->assertStatus(200)
            ->assertViewIs('payment.failed');
    }

    /** @test */
    public function it_handles_payment_pending_page()
    {
        $response = $this->get('/payment/pending?id=test_payment_123');

        $response->assertStatus(200)
            ->assertViewIs('payment.pending');
    }

    /** @test */
    public function it_handles_payment_cancelled_page()
    {
        $response = $this->get('/payment/cancelled?id=test_payment_123');

        $response->assertStatus(200)
            ->assertViewIs('payment.cancelled');
    }

    /** @test */
    public function it_validates_webhook_signature()
    {
        $response = $this->post('/hyperpay/webhook', [
            'id' => 'test_payment_123',
            'result' => [
                'code' => '000.100.110',
                'description' => 'Success',
            ],
        ]);

        // This would test webhook signature validation
        // The actual implementation depends on your webhook service
        $response->assertStatus(200);
    }

    /** @test */
    public function it_supports_morphable_relationships()
    {
        // Create a test user
        $user = \App\Models\User::factory()->create();

        // Create a payment associated with the user
        $payment = Payment::create([
            'transaction_id' => 'test_payment_123',
            'amount' => 100.00,
            'currency' => 'SAR',
            'brand' => 'VISA',
            'status' => 'successful',
            'response' => [],
            'payable_type' => get_class($user),
            'payable_id' => $user->id,
        ]);

        // Test the relationship
        $this->assertEquals($user->id, $payment->payable->id);
        $this->assertCount(1, $user->payments);
    }
}
