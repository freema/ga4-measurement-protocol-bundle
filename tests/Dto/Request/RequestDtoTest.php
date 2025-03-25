<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\Dto\Request;

use Freema\GA4MeasurementProtocolBundle\Dto\Event\EventInterface;
use Freema\GA4MeasurementProtocolBundle\Dto\Request\RequestDto;
use Freema\GA4MeasurementProtocolBundle\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

class RequestDtoTest extends TestCase
{
    private RequestDto $request;

    protected function setUp(): void
    {
        $this->request = new RequestDto();
    }

    public function testClientId(): void
    {
        $this->assertNull($this->request->getClientId());
        
        $result = $this->request->setClientId('test-client-id');
        
        $this->assertSame($this->request, $result);
        $this->assertEquals('test-client-id', $this->request->getClientId());
    }

    public function testUserId(): void
    {
        $this->assertNull($this->request->getUserId());
        
        $result = $this->request->setUserId('test-user-id');
        
        $this->assertSame($this->request, $result);
        $this->assertEquals('test-user-id', $this->request->getUserId());
    }

    public function testApiSecret(): void
    {
        $this->assertNull($this->request->getApiSecret());
        
        $result = $this->request->setApiSecret('test-api-secret');
        
        $this->assertSame($this->request, $result);
        $this->assertEquals('test-api-secret', $this->request->getApiSecret());
    }

    public function testMeasurementId(): void
    {
        $this->assertNull($this->request->getMeasurementId());
        
        $result = $this->request->setMeasurementId('G-TEST123');
        
        $this->assertSame($this->request, $result);
        $this->assertEquals('G-TEST123', $this->request->getMeasurementId());
    }

    public function testAppInstanceId(): void
    {
        $this->assertNull($this->request->getAppInstanceId());
        
        $result = $this->request->setAppInstanceId('test-app-instance-id');
        
        $this->assertSame($this->request, $result);
        $this->assertEquals('test-app-instance-id', $this->request->getAppInstanceId());
    }

    public function testNonPersonalizedAds(): void
    {
        $this->assertFalse($this->request->getNonPersonalizedAds());
        
        $result = $this->request->setNonPersonalizedAds(true);
        
        $this->assertSame($this->request, $result);
        $this->assertTrue($this->request->getNonPersonalizedAds());
    }

    public function testTimestampMicros(): void
    {
        $this->assertNull($this->request->getTimestampMicros());
        
        $timestamp = 1609459200000000; // 2021-01-01 00:00:00 UTC in microseconds
        $result = $this->request->setTimestampMicros($timestamp);
        
        $this->assertSame($this->request, $result);
        $this->assertEquals($timestamp, $this->request->getTimestampMicros());
    }

    public function testAddEvent(): void
    {
        $event = $this->createMock(EventInterface::class);
        
        $result = $this->request->addEvent($event);
        
        $this->assertSame($this->request, $result);
        $this->assertCount(1, $this->request->getEvents());
        $this->assertSame($event, $this->request->getEvents()[0]);
    }

    public function testExport(): void
    {
        $event = $this->createMock(EventInterface::class);
        $event->method('export')->willReturn(['name' => 'test_event', 'params' => []]);
        
        $this->request->setClientId('test-client-id');
        $this->request->setUserId('test-user-id');
        $this->request->setTimestampMicros(1609459200000000);
        $this->request->addEvent($event);
        
        $result = $this->request->export();
        
        $this->assertIsArray($result);
        $this->assertEquals('test-client-id', $result['client_id']);
        $this->assertEquals('test-user-id', $result['user_id']);
        $this->assertEquals(1609459200000000, $result['timestamp_micros']);
        $this->assertIsArray($result['events']);
        $this->assertCount(1, $result['events']);
        $this->assertEquals(['name' => 'test_event', 'params' => []], $result['events'][0]);
    }

    public function testValidateWeb(): void
    {
        $event = $this->createMock(EventInterface::class);
        $event->method('validate')->willReturn(true);
        
        $this->request->setClientId('test-client-id');
        $this->request->addEvent($event);
        
        $this->assertTrue($this->request->validate('web'));
    }

    public function testValidateFirebase(): void
    {
        $event = $this->createMock(EventInterface::class);
        $event->method('validate')->willReturn(true);
        
        $this->request->setAppInstanceId('test-app-instance-id');
        $this->request->addEvent($event);
        
        $this->assertTrue($this->request->validate('firebase'));
    }

    public function testValidateThrowsExceptionForMissingClientId(): void
    {
        $event = $this->createMock(EventInterface::class);
        $this->request->addEvent($event);
        
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Parameter "client_id" is required for web context');
        
        $this->request->validate('web');
    }

    public function testValidateThrowsExceptionForMissingAppInstanceId(): void
    {
        $event = $this->createMock(EventInterface::class);
        $this->request->addEvent($event);
        
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Parameter "app_instance_id" is required for firebase context');
        
        $this->request->validate('firebase');
    }

    public function testValidateThrowsExceptionForBothClientIdAndAppInstanceId(): void
    {
        $event = $this->createMock(EventInterface::class);
        $this->request->setClientId('test-client-id');
        $this->request->setAppInstanceId('test-app-instance-id');
        $this->request->addEvent($event);
        
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Cannot specify both "client_id" and "app_instance_id"');
        
        $this->request->validate();
    }

    public function testValidateThrowsExceptionForMissingEvents(): void
    {
        $this->request->setClientId('test-client-id');
        
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('At least one event is required');
        
        $this->request->validate();
    }
}