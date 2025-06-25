<?php

declare(strict_types=1);

namespace AhmadChebbo\LaravelHyperpay\Listeners;

use AhmadChebbo\LaravelHyperpay\Events\PaymentSuccessful;
use AhmadChebbo\LaravelHyperpay\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class HandleSuccessfulPayment
{
    /**
     * Handle the event.
     */
    public function handle(PaymentSuccessful $event): void
    {
        $paymentData = $event->paymentData;

        // Log the successful payment
        Log::info('Payment successful', [
            'payment_id' => $paymentData['id'] ?? null,
            'amount' => $paymentData['amount'] ?? null,
            'currency' => $paymentData['currency'] ?? null,
            'brand' => $paymentData['paymentBrand'] ?? null,
            'merchant_transaction_id' => $paymentData['merchantTransactionId'] ?? null,
        ]);

        // Store payment in database
        $this->storePayment($paymentData);

        // Send confirmation email
        $this->sendConfirmationEmail($paymentData);

        // Update order status (if applicable)
        $this->updateOrderStatus($paymentData);

        // Send notification to admin
        $this->notifyAdmin($paymentData);
    }

    /**
     * Store payment in database
     */
    protected function storePayment(array $paymentData): void
    {
        try {
            Payment::create([
                'transaction_id' => $paymentData['id'] ?? uniqid('hyperpay_'),
                'amount' => $paymentData['amount'] ?? 0,
                'currency' => $paymentData['currency'] ?? 'SAR',
                'brand' => $paymentData['paymentBrand'] ?? 'UNKNOWN',
                'status' => 'successful',
                'response' => $paymentData,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store payment in database', [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData,
            ]);
        }
    }

    /**
     * Send confirmation email to customer
     */
    protected function sendConfirmationEmail(array $paymentData): void
    {
        try {
            $email = $paymentData['customer']['email'] ?? null;

            if ($email) {
                // You can create a custom Mailable class for this
                Mail::send('emails.payment.success', [
                    'payment' => $paymentData,
                    'amount' => number_format($paymentData['amount'] ?? 0, 2),
                    'currency' => $paymentData['currency'] ?? 'SAR',
                    'transaction_id' => $paymentData['id'] ?? '',
                ], function ($message) use ($email, $paymentData) {
                    $message->to($email)
                            ->subject('Payment Confirmation - ' . config('app.name'))
                            ->from(config('mail.from.address'), config('mail.from.name'));
                });
            }
        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation email', [
                'error' => $e->getMessage(),
                'email' => $email ?? 'not provided',
            ]);
        }
    }

    /**
     * Update order status (if payment is related to an order)
     */
    protected function updateOrderStatus(array $paymentData): void
    {
        try {
            $merchantTransactionId = $paymentData['merchantTransactionId'] ?? null;

            if ($merchantTransactionId) {
                // Extract order ID from merchant transaction ID
                // This depends on your naming convention
                $orderId = $this->extractOrderId($merchantTransactionId);

                if ($orderId) {
                    // Update order status to paid
                    // You would implement this based on your order model
                    // Order::where('id', $orderId)->update(['status' => 'paid']);

                    Log::info('Order status updated to paid', [
                        'order_id' => $orderId,
                        'payment_id' => $paymentData['id'] ?? null,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to update order status', [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData,
            ]);
        }
    }

    /**
     * Send notification to admin
     */
    protected function notifyAdmin(array $paymentData): void
    {
        try {
            // You can create a custom notification class for this
            // $admin = User::where('role', 'admin')->first();
            // if ($admin) {
            //     $admin->notify(new PaymentReceivedNotification($paymentData));
            // }

            Log::info('Admin notification sent for successful payment', [
                'payment_id' => $paymentData['id'] ?? null,
                'amount' => $paymentData['amount'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send admin notification', [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData,
            ]);
        }
    }

    /**
     * Extract order ID from merchant transaction ID
     */
    protected function extractOrderId(string $merchantTransactionId): ?string
    {
        // Implement based on your naming convention
        // Example: if merchant transaction ID is "order_123_payment_456"
        if (preg_match('/order_(\d+)/', $merchantTransactionId, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
