<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Exception;

class ValidationException extends \Exception
{
    private string $field;
    private int $errorCode;

    public function __construct(string $message, int $errorCode, string $field, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->errorCode = $errorCode;
        $this->field = $field;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }
}
