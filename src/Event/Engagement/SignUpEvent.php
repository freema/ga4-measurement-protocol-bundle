<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Event\Engagement;

use Freema\GA4MeasurementProtocolBundle\Event\AbstractEvent;

/**
 * Sign up event for GA4.
 */
class SignUpEvent extends AbstractEvent
{
    public function getName(): string
    {
        return 'sign_up';
    }

    /**
     * Set sign up method.
     */
    public function setMethod(string $method): self
    {
        $this->parameters['method'] = $method;

        return $this;
    }
}
