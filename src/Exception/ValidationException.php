<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Exception;

use Freema\GA4MeasurementProtocolBundle\Dto\Common\ValidationMessage;

class ValidationException extends AnalyticsException
{
    /**
     * @var ?string
     */
    protected ?string $field = null;

    /**
     * @var ?ValidationMessage
     */
    protected ?ValidationMessage $validationMessage = null;

    /**
     * ValidationException constructor.
     * @param string $message
     * @param int $code
     * @param string|null $field
     */
    public function __construct(string $message = "", int $code = 0, ?string $field = null)
    {
        parent::__construct($message, $code);

        $this->field = $field;
        $this->validationMessage = new ValidationMessage();
        $this->validationMessage->setValidationCode($code);
        $this->validationMessage->setValidationMessage($message);
        $this->validationMessage->setFieldPath($field);
    }

    /**
     * @return ValidationMessage|null
     */
    public function getValidationMessage(): ?ValidationMessage
    {
        return $this->validationMessage;
    }

    /**
     * @param ValidationMessage|null $validationMessage
     */
    public function setValidationMessage(?ValidationMessage $validationMessage): void
    {
        $this->validationMessage = $validationMessage;
    }

    /**
     * @return string|null
     */
    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * @param string|null $field
     */
    public function setField(?string $field): void
    {
        $this->field = $field;
    }
}
