<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Event;

/**
 * Basic interface for all GA4 events.
 */
interface EventInterface extends ValidateInterface
{
    /**
     * Get the event name for GA4.
     */
    public function getName(): string;

    /**
     * Get all parameters for this event.
     */
    public function getParameters(): array;

    /**
     * Add a custom parameter to the event.
     */
    public function addParameter(string $key, mixed $value): self;
}
