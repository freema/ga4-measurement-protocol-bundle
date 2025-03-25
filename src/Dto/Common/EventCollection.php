<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Common;

use Freema\GA4MeasurementProtocolBundle\Dto\Event\AbstractEvent;
use Freema\GA4MeasurementProtocolBundle\Dto\ExportableInterface;
use Freema\GA4MeasurementProtocolBundle\Dto\ValidateInterface;
use Freema\GA4MeasurementProtocolBundle\Enum\ErrorCode;
use Freema\GA4MeasurementProtocolBundle\Exception\ValidationException;

class EventCollection implements ExportableInterface, ValidateInterface
{
    /**
     * @var AbstractEvent[]
     */
    protected array $eventList = [];

    /**
     * @param AbstractEvent $event
     */
    public function addEvent(AbstractEvent $event): self
    {
        if (count($this->eventList) >= 25) {
            throw new \InvalidArgumentException('Event list must not exceed 25 items', ErrorCode::MAX_EVENT_COUNT_EXCEED);
        }

        $this->eventList[] = $event;
        return $this;
    }

    /**
     * @return array
     */
    public function export(): array
    {
        return array_map(function (AbstractEvent $event) {
            return $event->export();
        }, $this->getEventList());
    }

    /**
     * @return AbstractEvent[]
     */
    public function getEventList(): array
    {
        return $this->eventList;
    }

    /**
     * @param AbstractEvent[] $eventList
     */
    public function setEventList(array $eventList): self
    {
        if (count($eventList) > 25) {
            throw new \InvalidArgumentException('Event list must not exceed 25 items', ErrorCode::MAX_EVENT_COUNT_EXCEED);
        }

        $this->eventList = $eventList;
        return $this;
    }

    /**
     * @throws ValidationException
     */
    public function validate(): bool
    {
        if (count($this->getEventList()) === 0) {
            throw new ValidationException('Event list must not be empty', ErrorCode::VALIDATION_EVENTS_MUST_NOT_BE_EMPTY, 'events');
        }

        foreach ($this->getEventList() as $event) {
            $event->validate();
        }

        return true;
    }
}
