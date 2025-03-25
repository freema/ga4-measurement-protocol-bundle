<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Event;

/**
 * GA4 Custom event - can be used for any event type.
 */
class CustomEvent extends AbstractEventDto
{
    /**
     * CustomEvent constructor.
     */
    public function __construct(string $eventName)
    {
        parent::__construct($eventName);
    }
}