<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Common;

use Freema\GA4MeasurementProtocolBundle\Dto\ExportableInterface;

class ValidationMessage implements ExportableInterface
{
    /**
     * @var int
     */
    protected int $validationCode;

    /**
     * @var string
     */
    protected string $validationMessage;

    /**
     * @var ?string
     */
    protected ?string $fieldPath = null;

    /**
     * @return array
     */
    public function export(): array
    {
        return array_filter([
            'validation_code' => $this->getValidationCode(),
            'validation_message' => $this->getValidationMessage(),
            'field_path' => $this->getFieldPath()
        ]);
    }

    /**
     * @return int
     */
    public function getValidationCode(): int
    {
        return $this->validationCode;
    }

    /**
     * @param int $validationCode
     * @return ValidationMessage
     */
    public function setValidationCode(int $validationCode): self
    {
        $this->validationCode = $validationCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getValidationMessage(): string
    {
        return $this->validationMessage;
    }

    /**
     * @param string $validationMessage
     * @return ValidationMessage
     */
    public function setValidationMessage(string $validationMessage): self
    {
        $this->validationMessage = $validationMessage;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFieldPath(): ?string
    {
        return $this->fieldPath;
    }

    /**
     * @param string|null $fieldPath
     * @return ValidationMessage
     */
    public function setFieldPath(?string $fieldPath): self
    {
        $this->fieldPath = $fieldPath;
        return $this;
    }
}
