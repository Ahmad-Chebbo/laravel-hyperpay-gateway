<?php

namespace AhmadShebbo\LaravelHyperpay\Exceptions;

use Exception;

class InvalidBrandException extends HyperPayException
{
    public function __construct(
        string $message = 'Invalid brand',
        int $code = 0,
        ?Exception $previous = null,
        ?string $hyperPayCode = null,
        ?array $context = null
    ) {
        parent::__construct($message, $code, $previous, $hyperPayCode, $context);
    }
}
