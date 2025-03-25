<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Response;

use Freema\GA4MeasurementProtocolBundle\Dto\Common\ValidationMessage;
use Freema\GA4MeasurementProtocolBundle\Enum\ValidationCode;
use Freema\GA4MeasurementProtocolBundle\Exception\HydrationException;
use Psr\Http\Message\ResponseInterface;

class DebugResponse extends BaseResponse
{
    /**
     * @var array
     */
    protected array $validationMessages = [];

    /**
     * @var bool
     */
    protected bool $validationPassed = false;

    /**
     * DebugResponse constructor.
     * @param ResponseInterface|null $response
     * @throws HydrationException
     */
    public function __construct(?ResponseInterface $response = null)
    {
        parent::__construct($response);
    }

    /**
     * @param ResponseInterface|array $blueprint
     * @throws HydrationException
     */
    public function hydrate($blueprint)
    {
        if ($blueprint instanceof ResponseInterface) {
            parent::hydrate($blueprint);

            try {
                $jsonResponse = $this->getJsonResponse();
                $this->parseValidation($jsonResponse);
            } catch (\Exception $e) {
                throw new HydrationException('Error while parsing response. Error: ' . $e->getMessage(), 0, $e);
            }
        } else {
            throw new HydrationException('Unsupported hydration source');
        }
    }

    /**
     * @return array
     */
    protected function getJsonResponse(): array
    {
        return json_decode($this->getBody() ?? '', true) ?? [];
    }

    /**
     * @param array $jsonResponse
     */
    protected function parseValidation(array $jsonResponse)
    {
        if (isset($jsonResponse['validationMessages'])) {
            $this->parseValidationMessages($jsonResponse['validationMessages']);
        }

        $this->validationPassed = !empty($jsonResponse) && empty($this->getValidationMessages());
    }

    /**
     * @param array $validationMessagesArray
     */
    protected function parseValidationMessages(array $validationMessagesArray)
    {
        $this->validationMessages = [];

        foreach ($validationMessagesArray as $validationMessageData) {
            $validationMessage = new ValidationMessage();
            $validationMessage->setValidationCode(ValidationCode::VALIDATION_ERROR->value);

            if (isset($validationMessageData['fieldPath'])) {
                $validationMessage->setFieldPath($validationMessageData['fieldPath']);
            }

            if (isset($validationMessageData['description'])) {
                $validationMessage->setValidationMessage($validationMessageData['description']);
            }

            $this->validationMessages[] = $validationMessage;
        }
    }

    /**
     * @return array
     */
    public function export(): array
    {
        $baseExport = parent::export();

        return array_merge($baseExport, [
            'validation_passed' => $this->isValidationPassed(),
            'validation_messages' => array_map(function (ValidationMessage $validationMessage) {
                return $validationMessage->export();
            }, $this->getValidationMessages())
        ]);
    }

    /**
     * @return ValidationMessage[]
     */
    public function getValidationMessages(): array
    {
        return $this->validationMessages;
    }

    /**
     * @param ValidationMessage[] $validationMessages
     * @return DebugResponse
     */
    public function setValidationMessages(array $validationMessages): self
    {
        $this->validationMessages = $validationMessages;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValidationPassed(): bool
    {
        return $this->validationPassed;
    }

    /**
     * @param bool $validationPassed
     * @return DebugResponse
     */
    public function setValidationPassed(bool $validationPassed): self
    {
        $this->validationPassed = $validationPassed;
        return $this;
    }
}
