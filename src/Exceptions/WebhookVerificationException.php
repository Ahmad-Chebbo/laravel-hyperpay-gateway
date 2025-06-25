<?php

namespace AhmadChebbo\LaravelHyperpay\Exceptions;

use Exception;

class WebhookVerificationException extends Exception
{
    /**
     * Create a new webhook verification exception instance.
     */
    public function __construct(string $message = 'Webhook verification failed', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Report the exception.
     */
    public function report(): void
    {
        // Log the webhook verification failure
        logger()->error('Webhook verification failed', [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
        ]);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Webhook verification failed',
                'message' => $this->getMessage(),
            ], 401);
        }

        return response()->json([
            'error' => 'Webhook verification failed',
            'message' => $this->getMessage(),
        ], 401);
    }
}
