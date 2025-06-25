<?php

namespace Ahmad-Chebbo\LaravelHyperpay\Services;

use Ahmad-Chebbo\LaravelHyperpay\DTOs\CheckoutRequest;
use Ahmad-Chebbo\LaravelHyperpay\DTOs\CheckoutResponse;
use Ahmad-Chebbo\LaravelHyperpay\DTOs\PaymentRequest;
use Ahmad-Chebbo\LaravelHyperpay\DTOs\PaymentResponse;
use Ahmad-Chebbo\LaravelHyperpay\Exceptions\HyperPayException;
use Ahmad-Chebbo\LaravelHyperpay\Exceptions\InvalidAmountException;
use Ahmad-Chebbo\LaravelHyperpay\Exceptions\InvalidBrandException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HyperPayService
{
    protected array $config;

    protected string $environment;

    protected string $baseUrl;

    protected string $token;

    protected array $entities;

    public function __construct()
    {
        $this->config = config('hyperpay');
        $this->environment = $this->config['environment'];

        $envConfig = $this->config[$this->environment];
        $this->baseUrl = $envConfig['url'];
        $this->token = $envConfig['token'];
        $this->entities = $envConfig['entities'];

        $this->validateConfiguration();
    }

    /**
     * Create a checkout session (hosted payment page)
     */
    public function createCheckout(CheckoutRequest $request): CheckoutResponse
    {
        $this->validateAmount($request->amount);
        $this->validateBrand($request->brand);

        $entityId = $this->getEntityId($request->brand);

        $params = [
            'entityId' => $entityId,
            'amount' => number_format($request->amount, 2, '.', ''),
            'currency' => $request->currency ?? $this->config['currency'],
            'paymentType' => $request->paymentType ?? $this->config['payment_type'],
        ];

        // Add optional parameters
        if ($request->merchantTransactionId) {
            $params['merchantTransactionId'] = $request->merchantTransactionId;
        }

        if ($request->customer) {
            $params = array_merge($params, $request->customer->toArray());
        }

        if ($request->billing) {
            $params = array_merge($params, $request->billing->toArray());
        }

        if ($request->shipping) {
            $params = array_merge($params, $request->shipping->toArray());
        }

        // Add custom parameters
        if ($request->customParameters) {
            $params = array_merge($params, $request->customParameters);
        }

        // Add risk parameters
        if ($request->riskParameters) {
            $params = array_merge($params, $request->riskParameters);
        }

        // Card tokenization support
        if ($request->createRegistration !== null) {
            $params['createRegistration'] = $request->createRegistration ? 'true' : 'false';
        }
        if ($request->registrationId) {
            $params['registrationId'] = $request->registrationId;
        }

        $response = $this->makeRequest('POST', '/v1/checkouts', $params);

        $this->logTransaction('checkout', $params, $response);

        // Attach registrationId if present
        if (isset($response['registrationId'])) {
            $response['card_token'] = $response['registrationId'];
        }

        return new CheckoutResponse($response);
    }

    /**
     * Process direct payment (server-to-server)
     */
    public function processPayment(PaymentRequest $request): PaymentResponse
    {
        $this->validateAmount($request->amount);
        $this->validateBrand($request->brand);

        // If registrationId is provided, do not require card data
        if (! $request->registrationId) {
            $this->validateCardData($request);
        }

        $entityId = $this->getEntityId($request->brand);

        $params = [
            'entityId' => $entityId,
            'amount' => number_format($request->amount, 2, '.', ''),
            'currency' => $request->currency ?? $this->config['currency'],
            'paymentType' => $request->paymentType ?? $this->config['payment_type'],
            'paymentBrand' => $request->brand,
            'merchantTransactionId' => $request->merchantTransactionId ?? uniqid('hyperpay_'),
        ];

        // Card data or registrationId
        if ($request->registrationId) {
            $params['registrationId'] = $request->registrationId;
        } else {
            $params['card.number'] = $request->cardNumber;
            $params['card.holder'] = $request->cardHolder;
            $params['card.expiryMonth'] = str_pad($request->expiryMonth, 2, '0', STR_PAD_LEFT);
            $params['card.expiryYear'] = $request->expiryYear;
            $params['card.cvv'] = $request->cvv;
        }

        // Tokenization
        if ($request->createRegistration !== null) {
            $params['createRegistration'] = $request->createRegistration ? 'true' : 'false';
        }

        // Add result URLs
        if (property_exists($request, 'shopperResultUrl') && $request->shopperResultUrl) {
            $params['shopperResultUrl'] = $request->shopperResultUrl;
        } elseif ($this->config['customization']['result_url']) {
            $params['shopperResultUrl'] = $this->config['customization']['result_url'];
        }

        // Add optional parameters
        if ($request->customer) {
            $params = array_merge($params, $request->customer->toArray());
        }

        if ($request->billing) {
            $params = array_merge($params, $request->billing->toArray());
        }

        if ($request->shipping) {
            $params = array_merge($params, $request->shipping->toArray());
        }

        // Add 3D Secure parameters
        if ($request->threeDSecure) {
            $params = array_merge($params, $request->threeDSecure);
        }

        $response = $this->makeRequest('POST', '/v1/payments', $params);

        $this->logTransaction('payment', $params, $response);

        // Attach registrationId if present
        if (isset($response['registrationId'])) {
            $response['card_token'] = $response['registrationId'];
        }

        return new PaymentResponse($response);
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $paymentId, ?string $brand = null): PaymentResponse
    {
        if (! $brand) {
            // Try to determine brand from payment ID or use a default entity
            $brand = 'VISA'; // Default fallback
        }

        $this->validateBrand($brand);
        $entityId = $this->getEntityId($brand);

        $response = $this->makeRequest('GET', "/v1/payments/{$paymentId}", [
            'entityId' => $entityId,
        ]);

        $this->logTransaction('status_check', ['paymentId' => $paymentId], $response);

        return new PaymentResponse($response);
    }

    /**
     * Process refund
     */
    public function processRefund(string $paymentId, float $amount, string $brand, ?string $reason = null): PaymentResponse
    {
        $this->validateAmount($amount);
        $this->validateBrand($brand);

        $entityId = $this->getEntityId($brand);

        $params = [
            'entityId' => $entityId,
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => $this->config['currency'],
            'paymentType' => 'RF', // Refund
        ];

        if ($reason) {
            $params['descriptor'] = $reason;
        }

        $response = $this->makeRequest('POST', "/v1/payments/{$paymentId}", $params);

        $this->logTransaction('refund', $params, $response);

        return new PaymentResponse($response);
    }

    /**
     * Process capture (for pre-authorized payments)
     */
    public function capturePayment(string $paymentId, float $amount, string $brand): PaymentResponse
    {
        $this->validateAmount($amount);
        $this->validateBrand($brand);

        $entityId = $this->getEntityId($brand);

        $params = [
            'entityId' => $entityId,
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => $this->config['currency'],
            'paymentType' => 'CP', // Capture
        ];

        $response = $this->makeRequest('POST', "/v1/payments/{$paymentId}", $params);

        $this->logTransaction('capture', $params, $response);

        return new PaymentResponse($response);
    }

    /**
     * Process reversal (void)
     */
    public function reversePayment(string $paymentId, string $brand): PaymentResponse
    {
        $this->validateBrand($brand);

        $entityId = $this->getEntityId($brand);

        $params = [
            'entityId' => $entityId,
            'paymentType' => 'RV', // Reversal
        ];

        $response = $this->makeRequest('POST', "/v1/payments/{$paymentId}", $params);

        $this->logTransaction('reversal', $params, $response);

        return new PaymentResponse($response);
    }

    /**
     * Get supported payment brands
     */
    public function getSupportedBrands(): array
    {
        return $this->config['supported_brands'];
    }

    /**
     * Check if brand is supported
     */
    public function isBrandSupported(string $brand): bool
    {
        return in_array(strtoupper($brand), $this->getSupportedBrands());
    }

    /**
     * Get entity ID for brand
     */
    protected function getEntityId(string $brand): string
    {
        $brand = strtolower($brand);

        if (! isset($this->entities[$brand])) {
            throw new InvalidBrandException("Entity ID not configured for brand: {$brand}");
        }

        $entityId = $this->entities[$brand];

        if (empty($entityId)) {
            throw new InvalidBrandException("Entity ID is empty for brand: {$brand}");
        }

        return $entityId;
    }

    /**
     * Make HTTP request to HyperPay API
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            $request = Http::withHeaders([
                'Authorization' => "Bearer {$this->token}",
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->timeout($this->config['customization']['timeout'] ?? 30);

            if ($method === 'GET') {
                $response = $request->get($this->baseUrl.$endpoint, $data);
            } else {
                $response = $request->asForm()->post($this->baseUrl.$endpoint, $data);
            }

            if (! $response->successful()) {
                throw new HyperPayException(
                    'HyperPay API request failed: '.$response->body(),
                    $response->status()
                );
            }

            return $response->json();

        } catch (\Exception $e) {
            $this->logError('API Request Failed', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            throw new HyperPayException(
                'Failed to communicate with HyperPay API: '.$e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Validate configuration
     */
    protected function validateConfiguration(): void
    {
        if (empty($this->token)) {
            throw new HyperPayException("HyperPay token is not configured for {$this->environment} environment");
        }

        if (empty($this->baseUrl)) {
            throw new HyperPayException("HyperPay URL is not configured for {$this->environment} environment");
        }
    }

    /**
     * Validate amount
     */
    protected function validateAmount(float $amount): void
    {
        $minAmount = $this->config['risk_management']['min_amount'] ?? 1;
        $maxAmount = $this->config['risk_management']['max_amount'] ?? null;

        if ($amount < $minAmount) {
            throw new InvalidAmountException("Amount must be at least {$minAmount}");
        }

        if ($maxAmount && $amount > $maxAmount) {
            throw new InvalidAmountException("Amount cannot exceed {$maxAmount}");
        }
    }

    /**
     * Validate payment brand
     */
    protected function validateBrand(string $brand): void
    {
        if (! $this->isBrandSupported($brand)) {
            throw new InvalidBrandException("Unsupported payment brand: {$brand}");
        }
    }

    /**
     * Validate card data for direct payments
     */
    protected function validateCardData(PaymentRequest $request): void
    {
        if (empty($request->cardNumber)) {
            throw new HyperPayException('Card number is required');
        }

        if (empty($request->cardHolder)) {
            throw new HyperPayException('Card holder name is required');
        }

        if (empty($request->expiryMonth) || empty($request->expiryYear)) {
            throw new HyperPayException('Card expiry date is required');
        }

        if (empty($request->cvv)) {
            throw new HyperPayException('CVV is required');
        }

        // Basic validation
        if (! preg_match('/^\d{13,19}$/', $request->cardNumber)) {
            throw new HyperPayException('Invalid card number format');
        }

        if (! preg_match('/^(0[1-9]|1[0-2])$/', str_pad($request->expiryMonth, 2, '0', STR_PAD_LEFT))) {
            throw new HyperPayException('Invalid expiry month');
        }

        if (! preg_match('/^\d{2,4}$/', $request->expiryYear)) {
            throw new HyperPayException('Invalid expiry year');
        }

        if (! preg_match('/^\d{3,4}$/', $request->cvv)) {
            throw new HyperPayException('Invalid CVV');
        }
    }

    /**
     * Log transaction
     */
    protected function logTransaction(string $type, array $request, array $response): void
    {
        if (! $this->config['logging']['enabled']) {
            return;
        }

        // Remove sensitive data from logs
        $logRequest = $this->sanitizeLogData($request);
        $logResponse = $this->sanitizeLogData($response);

        Log::channel($this->config['logging']['channel'])
            ->log($this->config['logging']['level'], "HyperPay {$type} transaction", [
                'environment' => $this->environment,
                'request' => $logRequest,
                'response' => $logResponse,
            ]);
    }

    /**
     * Log error
     */
    protected function logError(string $message, array $context = []): void
    {
        if (! $this->config['logging']['enabled']) {
            return;
        }

        Log::channel($this->config['logging']['channel'])
            ->error("HyperPay Error: {$message}", $context);
    }

    /**
     * Remove sensitive data from logs
     */
    protected function sanitizeLogData(array $data): array
    {
        $sensitiveFields = [
            'card.number',
            'card.cvv',
            'customer.givenName',
            'customer.surname',
            'customer.email',
            'customer.phone',
            'billing.street1',
            'billing.city',
            'billing.postcode',
            'shipping.street1',
            'shipping.city',
            'shipping.postcode',
        ];

        $sanitized = $data;

        foreach ($sensitiveFields as $field) {
            if (isset($sanitized[$field])) {
                $sanitized[$field] = str_repeat('*', strlen($sanitized[$field]));
            }
        }

        return $sanitized;
    }
}
