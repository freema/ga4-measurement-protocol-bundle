<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Event;

use Freema\GA4MeasurementProtocolBundle\Exception\ValidationException;

/**
 * Interface for all event classes that require validation.
 */
interface ValidateInterface
{
    /**
     * Validate the event parameters.
     *
     * @return bool Returns true if validation passes
     *
     * @throws ValidationException If validation fails
     */
    public function validate(): bool;
}
