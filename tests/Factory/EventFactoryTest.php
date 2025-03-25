<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\Factory;

use Freema\GA4MeasurementProtocolBundle\Dto\Event\CustomEvent;
use Freema\GA4MeasurementProtocolBundle\Dto\Event\PageViewEvent;
use Freema\GA4MeasurementProtocolBundle\Dto\Event\PurchaseEvent;
use Freema\GA4MeasurementProtocolBundle\Dto\Item\ItemDto;
use Freema\GA4MeasurementProtocolBundle\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class EventFactoryTest extends TestCase
{
    private EventFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new EventFactory();
    }

    public function testCreatePageViewEvent(): void
    {
        $event = $this->factory->createPageViewEvent();
        
        $this->assertInstanceOf(PageViewEvent::class, $event);
        $this->assertEquals('page_view', $event->getName());
    }

    public function testCreatePurchaseEvent(): void
    {
        $event = $this->factory->createPurchaseEvent();
        
        $this->assertInstanceOf(PurchaseEvent::class, $event);
        $this->assertEquals('purchase', $event->getName());
    }

    public function testCreateCustomEvent(): void
    {
        $event = $this->factory->createCustomEvent('test_event');
        
        $this->assertInstanceOf(CustomEvent::class, $event);
        $this->assertEquals('test_event', $event->getName());
    }

    public function testCreateItem(): void
    {
        $item = $this->factory->createItem();
        
        $this->assertInstanceOf(ItemDto::class, $item);
    }

    public function testCreateItemFromArray(): void
    {
        $data = [
            'sku' => 'SKU-123',
            'name' => 'Test Product',
            'brand' => 'Test Brand',
            'price' => 99.99,
            'quantity' => 2,
        ];
        
        $item = $this->factory->createItemFromArray($data);
        
        $this->assertInstanceOf(ItemDto::class, $item);
        $this->assertEquals('SKU-123', $item->getId());
        $this->assertEquals('Test Product', $item->getName());
        $this->assertEquals('Test Brand', $item->getBrand());
        $this->assertEquals(99.99, $item->getPrice());
        $this->assertEquals(2, $item->getQuantity());
    }
}