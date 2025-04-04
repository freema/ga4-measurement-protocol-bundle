<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Event;

/**
 * Custom event for GA4 that allows specifying a custom event name.
 * This is a convenience class so users don't have to create their own AbstractEvent extensions.
 */
class CustomEvent extends AbstractEvent
{
    private string $eventName;

    /**
     * CustomEvent constructor.
     */
    public function __construct(string $eventName)
    {
        $this->eventName = $eventName;
    }

    public function getName(): string
    {
        return $this->eventName;
    }
}
