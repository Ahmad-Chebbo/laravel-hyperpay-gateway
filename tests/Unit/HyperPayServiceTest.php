<?php

declare(strict_types=1);

namespace AhmadChebbo\LaravelHyperpay\Tests\Unit;

use Tests\TestCase;
use AhmadChebbo\LaravelHyperpay\Services\HyperPayService;
use AhmadChebbo\LaravelHyperpay\DTOs\CheckoutRequest;
use AhmadChebbo\LaravelHyperpay\DTOs\PaymentRequest;
use AhmadChebbo\LaravelHyperpay\DTOs\CustomerData;
use AhmadChebbo\LaravelHyperpay\DTOs\BillingData;
use AhmadChebbo\LaravelHyperpay\Exceptions\InvalidAmountException;
use AhmadChebbo\LaravelHyperpay\Exceptions\InvalidBrandException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class HyperPayServiceTest extends TestCase
{
    protected HyperPayService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test configuration
        Config::set('hyperpay.environment', 'test');
        Config::set('hyperpay.test.url', 'https://eu-test.oppwa.com');
        Config::set('hyperpay.test.token', 'test_token');
        Config::set('hyperpay.test.entities.visa', 'test_visa_entity');
        Config::set('hyperpay.test.entities.master', 'test_master_entity');
        Config::set('hyperpay.test.entities.mada', 'test_mada_entity');
        Config::set('hyperpay.currency', 'SAR');
        Config::set('hyperpay.payment_type', 'DB');

        $this->service = new HyperPayService();
    }

    /** @test */
    public function it_can_create_checkout_request()
    {
        Http::fake([
            'https://eu-test.oppwa.com/v1/checkouts' => Http::response([
                'id' => 'test_checkout_123',
                'redirectUrl' => 'https://test.oppwa.com/checkout/test_checkout_123',
                'merchantTransactionId' => 'order_123',
            ], 200),
        ]);

        $request = new CheckoutRequest(
            amount: 100.00,
            brand: 'VISA',
            currency: 'SAR',
            merchantTransactionId: 'order_123',
        );

        $response = $this->service->createCheckout($request);

        $this->assertEquals('test_checkout_123', $response->id);
        $this->assertEquals('https://test.oppwa.com/checkout/test_checkout_123', $response->redirectUrl);
        $this->assertEquals('order_123', $response->merchantTransactionId);
    }

    /** @test */
    public function it_validates_amount()
    {
        $this->expectException(InvalidAmountException::class);

        $request = new CheckoutRequest(
            amount: -10.00,
            brand: 'VISA',
        );

        $this->service->createCheckout($request);
    }

    /** @test */
    public function it_validates_brand()
    {
        $this->expectException(InvalidBrandException::class);

        $request = new CheckoutRequest(
            amount: 100.00,
            brand: 'INVALID_BRAND',
        );

        $this->service->createCheckout($request);
    }

    /** @test */
    public function it_can_process_payment()
    {
        Http::fake([
            'https://eu-test.oppwa.com/v1/payments' => Http::response([
                'id' => 'test_payment_123',
                'result' => [
                    'code' => '000.100.110',
                    'description' => 'Request successfully processed'
                ],
                'amount' => '100.00',
                'currency' => 'SAR',
                'paymentBrand' => 'VISA',
                'merchantTransactionId' => 'order_123',
            ], 200),
        ]);

        $request = new PaymentRequest(
            amount: 100.00,
            brand: 'VISA',
            cardNumber: '4111111111111111',
            cardHolder: 'John Doe',
            expiryMonth: 12,
            expiryYear: 2025,
            cvv: '123',
        );

        $response = $this->service->processPayment($request);

        $this->assertEquals('test_payment_123', $response->id);
        $this->assertEquals('000.100.110', $response->getResultCode());
        $this->assertTrue($response->isSuccessful());
    }

    /** @test */
    public function it_can_get_payment_status()
    {
        Http::fake([
            'https://eu-test.oppwa.com/v1/payments/test_payment_123*' => Http::response([
                'id' => 'test_payment_123',
                'result' => [
                    'code' => '000.100.110',
                    'description' => 'Request successfully processed'
                ],
                'amount' => '100.00',
                'currency' => 'SAR',
                'paymentBrand' => 'VISA',
                'merchantTransactionId' => 'order_123',
            ], 200),
        ]);

        $response = $this->service->getPaymentStatus('test_payment_123', 'VISA');

        $this->assertEquals('test_payment_123', $response->id);
        $this->assertEquals('000.100.110', $response->getResultCode());
        $this->assertTrue($response->isSuccessful());
    }

    /** @test */
    public function it_can_process_refund()
    {
        Http::fake([
            'https://eu-test.oppwa.com/v1/payments/test_payment_123' => Http::response([
                'id' => 'test_refund_123',
                'result' => [
                    'code' => '000.100.110',
                    'description' => 'Refund processed successfully'
                ],
                'amount' => '50.00',
                'currency' => 'SAR',
                'paymentBrand' => 'VISA',
            ], 200),
        ]);

        $response = $this->service->processRefund(
            paymentId: 'test_payment_123',
            amount: 50.00,
            brand: 'VISA',
            reason: 'Customer requested partial refund'
        );

        $this->assertEquals('test_refund_123', $response->id);
        $this->assertEquals('000.100.110', $response->getResultCode());
        $this->assertTrue($response->isSuccessful());
    }

    /** @test */
    public function it_can_capture_payment()
    {
        Http::fake([
            'https://eu-test.oppwa.com/v1/payments/test_payment_123' => Http::response([
                'id' => 'test_capture_123',
                'result' => [
                    'code' => '000.100.110',
                    'description' => 'Payment captured successfully'
                ],
                'amount' => '100.00',
                'currency' => 'SAR',
                'paymentBrand' => 'VISA',
            ], 200),
        ]);

        $response = $this->service->capturePayment(
            paymentId: 'test_payment_123',
            amount: 100.00,
            brand: 'VISA'
        );

        $this->assertEquals('test_capture_123', $response->id);
        $this->assertEquals('000.100.110', $response->getResultCode());
        $this->assertTrue($response->isSuccessful());
    }

    /** @test */
    public function it_can_reverse_payment()
    {
        Http::fake([
            'https://eu-test.oppwa.com/v1/payments/test_payment_123' => Http::response([
                'id' => 'test_reverse_123',
                'result' => [
                    'code' => '000.100.110',
                    'description' => 'Payment reversed successfully'
                ],
                'amount' => '100.00',
                'currency' => 'SAR',
                'paymentBrand' => 'VISA',
            ], 200),
        ]);

        $response = $this->service->reversePayment(
            paymentId: 'test_payment_123',
            brand: 'VISA'
        );

        $this->assertEquals('test_reverse_123', $response->id);
        $this->assertEquals('000.100.110', $response->getResultCode());
        $this->assertTrue($response->isSuccessful());
    }

    /** @test */
    public function it_returns_supported_brands()
    {
        $brands = $this->service->getSupportedBrands();

        $this->assertIsArray($brands);
        $this->assertContains('VISA', $brands);
        $this->assertContains('MASTER', $brands);
        $this->assertContains('MADA', $brands);
    }

    /** @test */
    public function it_validates_brand_support()
    {
        $this->assertTrue($this->service->isBrandSupported('VISA'));
        $this->assertTrue($this->service->isBrandSupported('MASTER'));
        $this->assertTrue($this->service->isBrandSupported('MADA'));
        $this->assertFalse($this->service->isBrandSupported('INVALID_BRAND'));
    }

    /** @test */
    public function it_handles_api_errors()
    {
        Http::fake([
            'https://eu-test.oppwa.com/v1/checkouts' => Http::response([
                'result' => [
                    'code' => '800.400.500',
                    'description' => 'Invalid request'
                ]
            ], 400),
        ]);

        $request = new CheckoutRequest(
            amount: 100.00,
            brand: 'VISA',
        );

        $response = $this->service->createCheckout($request);

        $this->assertEquals('800.400.500', $response->getResultCode());
        $this->assertFalse($response->isSuccessful());
    }

    /** @test */
    public function it_handles_network_errors()
    {
        Http::fake([
            'https://eu-test.oppwa.com/v1/checkouts' => Http::response(null, 500),
        ]);

        $request = new CheckoutRequest(
            amount: 100.00,
            brand: 'VISA',
        );

        $this->expectException(\Exception::class);
        $this->service->createCheckout($request);
    }

    /** @test */
    public function it_formats_amount_correctly()
    {
        Http::fake([
            'https://eu-test.oppwa.com/v1/checkouts' => Http::response([
                'id' => 'test_checkout_123',
            ], 200),
        ]);

        $request = new CheckoutRequest(
            amount: 100.50,
            brand: 'VISA',
        );

        $this->service->createCheckout($request);

        Http::assertSent(function ($request) {
            return $request->data()['amount'] === '100.50';
        });
    }

    /** @test */
    public function it_includes_customer_data_in_request()
    {
        Http::fake([
            'https://eu-test.oppwa.com/v1/checkouts' => Http::response([
                'id' => 'test_checkout_123',
            ], 200),
        ]);

        $customer = new CustomerData(
            givenName: 'John',
            surname: 'Doe',
            email: 'john.doe@example.com',
            phone: '+966501234567'
        );

        $request = new CheckoutRequest(
            amount: 100.00,
            brand: 'VISA',
            customer: $customer,
        );

        $this->service->createCheckout($request);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return $data['givenName'] === 'John' &&
                   $data['surname'] === 'Doe' &&
                   $data['email'] === 'john.doe@example.com' &&
                   $data['phone'] === '+966501234567';
        });
    }

    /** @test */
    public function it_includes_billing_data_in_request()
    {
        Http::fake([
            'https://eu-test.oppwa.com/v1/checkouts' => Http::response([
                'id' => 'test_checkout_123',
            ], 200),
        ]);

        $billing = new BillingData(
            street1: 'King Fahd Road',
            city: 'Riyadh',
            state: 'Riyadh',
            postcode: '12345',
            country: 'SA'
        );

        $request = new CheckoutRequest(
            amount: 100.00,
            brand: 'VISA',
            billing: $billing,
        );

        $this->service->createCheckout($request);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return $data['billing.street1'] === 'King Fahd Road' &&
                   $data['billing.city'] === 'Riyadh' &&
                   $data['billing.state'] === 'Riyadh' &&
                   $data['billing.postcode'] === '12345' &&
                   $data['billing.country'] === 'SA';
        });
    }
}
