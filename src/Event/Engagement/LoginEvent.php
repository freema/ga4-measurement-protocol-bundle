<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Event\Engagement;

use Freema\GA4MeasurementProtocolBundle\Event\AbstractEvent;

/**
 * Login event for GA4.
 */
class LoginEvent extends AbstractEvent
{
    public function getName(): string
    {
        return 'login';
    }

    /**
     * Set login method.
     */
    public function setMethod(string $method): self
    {
        $this->parameters['method'] = $method;

        return $this;
    }
}
