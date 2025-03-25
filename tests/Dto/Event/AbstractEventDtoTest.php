<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\Dto\Event;

use Freema\GA4MeasurementProtocolBundle\Dto\Event\AbstractEventDto;
use Freema\GA4MeasurementProtocolBundle\Dto\ExportableInterface;
use PHPUnit\Framework\TestCase;

class AbstractEventDtoTest extends TestCase
{
    private AbstractEventDto $event;

    protected function setUp(): void
    {
        // Create a concrete implementation of AbstractEventDto for testing
        $this->event = new class('test_event') extends AbstractEventDto {
        };
    }

    public function testGetName(): void
    {
        $this->assertEquals('test_event', $this->event->getName());
    }

    public function testAddParameter(): void
    {
        $this->event->addParameter('test_param', 'test_value');
        $parameters = $this->event->getParameters();
        
        $this->assertArrayHasKey('test_param', $parameters);
        $this->assertEquals('test_value', $parameters['test_param']);
    }

    public function testAddParameterWithNestedExportable(): void
    {
        // Create a mock ExportableInterface
        $exportable = $this->createMock(ExportableInterface::class);
        $exportable->method('export')->willReturn(['nested' => 'value']);
        
        $this->event->addParameter('exportable_param', $exportable);
        
        $result = $this->event->export();
        
        $this->assertEquals(['nested' => 'value'], $result['params']['exportable_param']);
    }

    public function testExport(): void
    {
        $this->event->addParameter('string_param', 'string_value');
        $this->event->addParameter('int_param', 123);
        $this->event->addParameter('bool_param', true);
        
        $result = $this->event->export();
        
        $this->assertIsArray($result);
        $this->assertEquals('test_event', $result['name']);
        $this->assertInstanceOf(\ArrayObject::class, $result['params']);
        $this->assertEquals('string_value', $result['params']['string_param']);
        $this->assertEquals(123, $result['params']['int_param']);
        $this->assertEquals(true, $result['params']['bool_param']);
    }

    public function testValidate(): void
    {
        $this->assertTrue($this->event->validate());
    }

    public function testValidateThrowsExceptionForEmptyEventName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Event name cannot be empty');
        
        $event = new class('') extends AbstractEventDto {
        };
        
        $event->validate();
    }

    public function testFluidInterface(): void
    {
        $result = $this->event->addParameter('test', 'value');
        $this->assertSame($this->event, $result);
    }
}