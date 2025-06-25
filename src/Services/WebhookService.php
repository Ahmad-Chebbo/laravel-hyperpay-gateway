<?php

namespace AhmadChebbo\LaravelHyperpay\Services;

use AhmadChebbo\LaravelHyperpay\Events\PaymentStatusChanged;
use AhmadChebbo\LaravelHyperpay\Exceptions\WebhookVerificationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    protected array $config;

    protected HyperPayResultCodeService $resultService;

    public function __construct(HyperPayResultCodeService $resultService)
    {
        $this->config = config('hyperpay');
        $this->resultService = $resultService;
    }

    /**
     * Handle incoming webhook
     */
    public function handleWebhook(Request $request): array
    {
        // Verify webhook signature if enabled
        if ($this->config['webhook']['verify_signature']) {
            $this->verifySignature($request);
        }

        // Extract payment data
        $paymentData = $this->extractPaymentData($request);

        // Log webhook
        $this->logWebhook($paymentData);

        // Fire event
        event(new PaymentStatusChanged($paymentData));

        return $paymentData;
    }

    /**
     * Verify webhook signature
     */
    protected function verifySignature(Request $request): void
    {
        $environment = $this->config['environment'];
        $webhookKey = $this->config[$environment]['webhook_key'];

        if (empty($webhookKey)) {
            throw new WebhookVerificationException('Webhook key not configured');
        }

        $signature = $request->header('X-Hyperpay-Signature');
        $payload = $request->getContent();

        $expectedSignature = hash_hmac('sha256', $payload, $webhookKey);

        if (! hash_equals($expectedSignature, $signature)) {
            throw new WebhookVerificationException('Invalid webhook signature');
        }
    }

    /**
     * Extract payment data from webhook
     */
    protected function extractPaymentData(Request $request): array
    {
        $data = $request->all();

        return [
            'id' => $data['id'] ?? null,
            'paymentId' => $data['paymentId'] ?? null,
            'result' => [
                'code' => $data['result']['code'] ?? null,
                'description' => $data['result']['description'] ?? null,
            ],
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? null,
            'paymentBrand' => $data['paymentBrand'] ?? null,
            'paymentType' => $data['paymentType'] ?? null,
            'merchantTransactionId' => $data['merchantTransactionId'] ?? null,
            'timestamp' => $data['timestamp'] ?? null,
            'card' => $data['card'] ?? null,
            'customer' => $data['customer'] ?? null,
            'billing' => $data['billing'] ?? null,
            'shipping' => $data['shipping'] ?? null,
            'risk' => $data['risk'] ?? null,
            'customParameters' => $data['customParameters'] ?? null,
            'raw_data' => $data,
        ];
    }

    /**
     * Log webhook
     */
    protected function logWebhook(array $data): void
    {
        if (! $this->config['logging']['enabled']) {
            return;
        }

        // Remove sensitive data
        $logData = $this->sanitizeWebhookData($data);

        Log::channel($this->config['logging']['channel'])
            ->info('HyperPay webhook received', $logData);
    }

    /**
     * Sanitize webhook data for logging
     */
    protected function sanitizeWebhookData(array $data): array
    {
        $sanitized = $data;

        // Remove or mask sensitive fields
        $sensitiveFields = [
            'card.number',
            'card.bin',
            'customer.email',
            'customer.phone',
            'billing.street1',
            'billing.city',
            'billing.postcode',
            'shipping.street1',
            'shipping.city',
            'shipping.postcode',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($sanitized[$field])) {
                $sanitized[$field] = str_repeat('*', strlen($sanitized[$field]));
            }
        }

        // Remove raw_data to avoid duplication
        unset($sanitized['raw_data']);

        return $sanitized;
    }
}
