<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Event;

use Freema\GA4MeasurementProtocolBundle\Enum\ErrorCode;
use Freema\GA4MeasurementProtocolBundle\Exception\ValidationException;

/**
 * Base implementation for all GA4 events.
 */
abstract class AbstractEvent implements EventInterface
{
    /** @var array<string, mixed> */
    protected array $parameters = [];

    /**
     * Set document path (page_location parameter).
     */
    public function setDocumentPath(string $path, ?string $host = null): self
    {
        // If host is provided, we prepend it to the path
        $location = $host ? $host.$path : $path;
        $this->parameters['page_location'] = $location;

        return $this;
    }

    /**
     * Set document title (page_title parameter).
     */
    public function setDocumentTitle(string $title): self
    {
        $this->parameters['page_title'] = $title;

        return $this;
    }

    /**
     * Set document referrer (page_referrer parameter).
     */
    public function setDocumentReferrer(string $referrer): self
    {
        $this->parameters['page_referrer'] = $referrer;

        return $this;
    }

    /**
     * Set currency for the event.
     */
    public function setCurrency(string $currency): self
    {
        $this->parameters['currency'] = $currency;

        return $this;
    }

    /**
     * Add custom parameter to the event.
     */
    public function addParameter(string $key, mixed $value): self
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    /**
     * Get all parameters for this event.
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get a specific parameter value.
     *
     * @return mixed The parameter value or null if not set
     */
    public function getParameter(string $key)
    {
        return $this->parameters[$key] ?? null;
    }

    /**
     * Check if a parameter is set.
     */
    public function hasParameter(string $key): bool
    {
        return isset($this->parameters[$key]);
    }

    /**
     * Validate the base event parameters.
     *
     * Child classes should override this method and call parent::validate()
     * to ensure base validation is performed
     *
     * @return bool Returns true if validation passes
     *
     * @throws ValidationException If validation fails
     */
    public function validate(): bool
    {
        // Base validation logic
        // No required fields by default in the abstract class
        return true;
    }

    /**
     * Helper method to check if a parameter is set and not empty.
     */
    protected function validateRequiredParameter(string $param, string $fieldName, int $errorCode): void
    {
        if (empty($this->getParameter($param))) {
            throw new ValidationException(sprintf('Field "%s" is required', $fieldName), $errorCode, $param);
        }
    }

    /**
     * Helper method to validate currency when value is set.
     */
    protected function validateCurrencyWithValue(): void
    {
        if (!empty($this->getParameter('value'))) {
            if (empty($this->getParameter('currency'))) {
                throw new ValidationException('Field "currency" is required when "value" is set', ErrorCode::VALIDATION_FIELD_REQUIRED, 'currency');
            }
        }
    }
}
