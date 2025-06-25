<?php

namespace AhmadChebbo\LaravelHyperpay\Exceptions;

use Exception;

/**
 * Base HyperPay Exception
 */
class HyperPayException extends Exception
{
    protected ?string $hyperPayCode = null;

    protected ?array $context = null;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Exception $previous = null,
        ?string $hyperPayCode = null,
        ?array $context = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->hyperPayCode = $hyperPayCode;
        $this->context = $context;
    }

    public function getHyperPayCode(): ?string
    {
        return $this->hyperPayCode;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }
}
