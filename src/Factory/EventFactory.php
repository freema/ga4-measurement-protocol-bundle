<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Factory;

use Freema\GA4MeasurementProtocolBundle\Dto\Event\CustomEvent;
use Freema\GA4MeasurementProtocolBundle\Dto\Event\EventInterface;
use Freema\GA4MeasurementProtocolBundle\Dto\Event\PageViewEvent;
use Freema\GA4MeasurementProtocolBundle\Dto\Event\PurchaseEvent;
use Freema\GA4MeasurementProtocolBundle\Dto\Item\ItemDto;

/**
 * Factory for creating GA4 events.
 */
class EventFactory
{
    /**
     * Create a PageView event.
     */
    public function createPageViewEvent(): PageViewEvent
    {
        return new PageViewEvent();
    }
    
    /**
     * Create a Purchase event.
     */
    public function createPurchaseEvent(): PurchaseEvent
    {
        return new PurchaseEvent();
    }
    
    /**
     * Create a custom event with the specified name.
     */
    public function createCustomEvent(string $eventName): CustomEvent
    {
        return new CustomEvent($eventName);
    }
    
    /**
     * Create an item from an array.
     */
    public function createItemFromArray(array $data): ItemDto
    {
        return ItemDto::fromArray($data);
    }
    
    /**
     * Create an item.
     */
    public function createItem(): ItemDto
    {
        return new ItemDto();
    }
}