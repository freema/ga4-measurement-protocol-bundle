<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\Dto\Event;

use Freema\GA4MeasurementProtocolBundle\Dto\Event\PageViewEvent;
use PHPUnit\Framework\TestCase;

class PageViewEventTest extends TestCase
{
    private PageViewEvent $event;

    protected function setUp(): void
    {
        $this->event = new PageViewEvent();
    }

    public function testConstructor(): void
    {
        $this->assertEquals('page_view', $this->event->getName());
    }

    public function testSetPageTitle(): void
    {
        $result = $this->event->setPageTitle('Test Page');
        
        $this->assertSame($this->event, $result);
        $this->assertEquals('Test Page', $this->event->getParameters()['page_title']);
    }

    public function testSetPageLocation(): void
    {
        $result = $this->event->setPageLocation('/test-page');
        
        $this->assertSame($this->event, $result);
        $this->assertEquals('/test-page', $this->event->getParameters()['page_location']);
    }

    public function testSetPageReferrer(): void
    {
        $result = $this->event->setPageReferrer('https://referrer.com');
        
        $this->assertSame($this->event, $result);
        $this->assertEquals('https://referrer.com', $this->event->getParameters()['page_referrer']);
    }

    public function testExport(): void
    {
        $this->event
            ->setPageTitle('Test Page')
            ->setPageLocation('/test-page')
            ->setPageReferrer('https://referrer.com');
        
        $result = $this->event->export();
        
        $this->assertEquals('page_view', $result['name']);
        $this->assertEquals('Test Page', $result['params']['page_title']);
        $this->assertEquals('/test-page', $result['params']['page_location']);
        $this->assertEquals('https://referrer.com', $result['params']['page_referrer']);
    }

    public function testValidate(): void
    {
        $this->assertTrue($this->event->validate());
    }
}