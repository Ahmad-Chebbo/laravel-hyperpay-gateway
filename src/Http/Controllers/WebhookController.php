<?php

namespace AhmadShebbo\LaravelHyperpay\Http\Controllers;

use AhmadShebbo\LaravelHyperpay\Exceptions\WebhookVerificationException;
use AhmadShebbo\LaravelHyperpay\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Webhook Controller
 */
class WebhookController
{
    protected WebhookService $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Handle HyperPay webhook
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            $data = $this->webhookService->handleWebhook($request);

            return response()->json([
                'status' => 'success',
                'message' => 'Webhook processed successfully',
                'payment_id' => $data['paymentId'] ?? null,
            ]);

        } catch (WebhookVerificationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Webhook verification failed',
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Webhook processing failed',
            ], 500);
        }
    }
}
