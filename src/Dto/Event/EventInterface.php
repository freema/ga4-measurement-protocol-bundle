<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Event;

use Freema\GA4MeasurementProtocolBundle\Dto\ExportableInterface;
use Freema\GA4MeasurementProtocolBundle\Dto\ValidateInterface;

/**
 * Base interface for all GA4 events.
 */
interface EventInterface extends ExportableInterface, ValidateInterface
{
    /**
     * Get the event name.
     */
    public function getName(): string;
    
    /**
     * Add a custom parameter to the event.
     * 
     * @param string $name The parameter name
     * @param mixed $value The parameter value
     */
    public function addParameter(string $name, mixed $value): self;
    
    /**
     * Get all the event parameters.
     *
     * @return array<string, mixed>
     */
    public function getParameters(): array;
}