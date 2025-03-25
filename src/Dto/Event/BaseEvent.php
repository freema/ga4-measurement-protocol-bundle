<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Event;

use Freema\GA4MeasurementProtocolBundle\Exception\ValidationException;

class BaseEvent extends AbstractEvent
{
    /**
     * @param string|null $name
     */
    public function setName(?string $name): self
    {
        parent::setName($name);
        return $this;
    }

    /**
     * @return bool
     * @throws ValidationException
     */
    public function validate(): bool
    {
        foreach ($this->getParamList() as $parameter) {
            $parameter->validate();
        }

        return true;
    }
}
