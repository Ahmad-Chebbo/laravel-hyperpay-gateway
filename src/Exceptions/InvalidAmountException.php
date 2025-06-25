<?php

namespace AhmadChebbo\LaravelHyperpay\Exceptions;

use Exception;

class InvalidAmountException extends HyperPayException
{
    public function __construct(
        string $message = 'Invalid amount',
        int $code = 0,
        ?Exception $previous = null,
        ?string $hyperPayCode = null,
        ?array $context = null
    ) {
        parent::__construct($message, $code, $previous, $hyperPayCode, $context);
    }
}
