<?php

namespace AhmadShebbo\LaravelHyperpay\Http\Controllers;

use AhmadShebbo\LaravelHyperpay\Events\PaymentFailed;
use AhmadShebbo\LaravelHyperpay\Events\PaymentPending;
use AhmadShebbo\LaravelHyperpay\Events\PaymentSuccessful;
use AhmadShebbo\LaravelHyperpay\Services\HyperPayResultCodeService;
use AhmadShebbo\LaravelHyperpay\Services\HyperPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController
{
    protected HyperPayService $hyperPayService;

    protected HyperPayResultCodeService $resultCodeService;

    public function __construct(
        HyperPayService $hyperPayService,
        HyperPayResultCodeService $resultCodeService
    ) {
        $this->hyperPayService = $hyperPayService;
        $this->resultCodeService = $resultCodeService;
    }

    /**
     * Handle payment result page
     */
    public function result(Request $request): View
    {
        $resourcePath = $request->get('resourcePath');
        $brand = $request->get('brand', 'VISA');

        $paymentData = null;
        $error = null;

        if ($resourcePath) {
            try {
                // Extract payment ID from resource path
                $paymentId = basename($resourcePath);

                // Get payment status
                $response = $this->hyperPayService->getPaymentStatus($paymentId, $brand);
                $paymentData = $response->toArray();

                // Fire appropriate events
                if ($response->isSuccessful()) {
                    event(new PaymentSuccessful($paymentData));
                } elseif ($response->isPending()) {
                    event(new PaymentPending($paymentData));
                } else {
                    $reason = $this->resultCodeService->getDescription($response->getResultCode());
                    event(new PaymentFailed($paymentData, $reason));
                }

            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        return view('hyperpay::result', compact('paymentData', 'error'));
    }

    /**
     * Handle payment cancel page
     */
    public function cancel(Request $request): View
    {
        return view('hyperpay::cancel');
    }

    /**
     * Handle payment error page
     */
    public function error(Request $request): View
    {
        $error = $request->get('error', 'An unknown error occurred');

        return view('hyperpay::error', compact('error'));
    }

    /**
     * Get payment status (AJAX endpoint)
     */
    public function status(Request $request, string $paymentId): JsonResponse
    {
        try {
            $brand = $request->get('brand', 'VISA');

            $response = $this->hyperPayService->getPaymentStatus($paymentId, $brand);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $response->getId(),
                    'result_code' => $response->getResultCode(),
                    'result_description' => $response->getResultDescription(),
                    'is_successful' => $response->isSuccessful(),
                    'needs_review' => $response->needsReview(),
                    'is_pending' => $response->isPending(),
                    'is_rejected' => $response->isRejected(),
                    'amount' => $response->getAmount(),
                    'currency' => $response->getCurrency(),
                    'payment_brand' => $response->getPaymentBrand(),
                    'merchant_transaction_id' => $response->getMerchantTransactionId(),
                    'timestamp' => $response->getTimestamp(),
                    'category' => $this->resultCodeService->getCategory($response->getResultCode()),
                    'suggested_action' => $this->resultCodeService->getSuggestedAction($response->getResultCode()),
                    'can_retry' => $this->resultCodeService->canRetry($response->getResultCode()),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
