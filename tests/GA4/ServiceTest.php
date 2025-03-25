<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\GA4;

use Freema\GA4MeasurementProtocolBundle\Dto\Event\EventInterface;
use Freema\GA4MeasurementProtocolBundle\Dto\Request\RequestDto;
use Freema\GA4MeasurementProtocolBundle\Dto\Response\BaseResponse;
use Freema\GA4MeasurementProtocolBundle\Dto\Response\DebugResponse;
use Freema\GA4MeasurementProtocolBundle\Exception\MisconfigurationException;
use Freema\GA4MeasurementProtocolBundle\Exception\ValidationException;
use Freema\GA4MeasurementProtocolBundle\GA4\Service;
use Freema\GA4MeasurementProtocolBundle\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ServiceTest extends TestCase
{
    private HttpClientInterface|MockObject $httpClient;
    private Service $service;
    private RequestDto|MockObject $request;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new Service($this->httpClient, 'test-api-secret', 'G-TEST123');
        $this->request = $this->createMock(RequestDto::class);
    }

    public function testSend(): void
    {
        $this->request->expects($this->once())
            ->method('validate')
            ->with('web')
            ->willReturn(true);
        
        $this->request->expects($this->once())
            ->method('export')
            ->willReturn(['client_id' => 'test-client-id', 'events' => []]);
        
        $this->httpClient->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('google-analytics.com/mp/collect?api_secret=test-api-secret&measurement_id=G-TEST123'),
                ['client_id' => 'test-client-id', 'events' => []]
            )
            ->willReturn(['status' => 200, 'body' => '{}']);
        
        $response = $this->service->send($this->request);
        
        $this->assertInstanceOf(BaseResponse::class, $response);
    }

    public function testSendDebug(): void
    {
        $this->request->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        
        $this->request->expects($this->once())
            ->method('export')
            ->willReturn(['client_id' => 'test-client-id', 'events' => []]);
        
        $this->httpClient->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('google-analytics.com/debug/mp/collect?api_secret=test-api-secret&measurement_id=G-TEST123'),
                ['client_id' => 'test-client-id', 'events' => []]
            )
            ->willReturn(['status' => 200, 'body' => '{"validationMessages":[]}']);
        
        $response = $this->service->sendDebug($this->request);
        
        $this->assertInstanceOf(DebugResponse::class, $response);
    }

    public function testBuildEndpointUrl(): void
    {
        $url = $this->service->buildEndpointUrl();
        $this->assertEquals('https://google-analytics.com/mp/collect', $url);
        
        $debugUrl = $this->service->buildEndpointUrl(true);
        $this->assertEquals('https://google-analytics.com/debug/mp/collect', $debugUrl);
    }

    public function testGetApiParameters(): void
    {
        $params = $this->service->getApiParameters();
        
        $this->assertArrayHasKey('api_secret', $params);
        $this->assertArrayHasKey('measurement_id', $params);
        $this->assertEquals('test-api-secret', $params['api_secret']);
        $this->assertEquals('G-TEST123', $params['measurement_id']);
        $this->assertArrayNotHasKey('firebase_app_id', $params);
    }

    public function testGetApiParametersWithFirebaseId(): void
    {
        $service = new Service($this->httpClient, 'test-api-secret');
        $service->setFirebaseId('test-firebase-id');
        
        $params = $service->getApiParameters();
        
        $this->assertArrayHasKey('api_secret', $params);
        $this->assertArrayHasKey('firebase_app_id', $params);
        $this->assertEquals('test-api-secret', $params['api_secret']);
        $this->assertEquals('test-firebase-id', $params['firebase_app_id']);
        $this->assertArrayNotHasKey('measurement_id', $params);
    }

    public function testGetApiParametersThrowsExceptionForBothIds(): void
    {
        $service = new Service($this->httpClient, 'test-api-secret', 'G-TEST123');
        $service->setFirebaseId('test-firebase-id');
        
        $this->expectException(MisconfigurationException::class);
        $this->expectExceptionMessage("Cannot specify both 'measurement_id' and 'firebase_app_id'.");
        
        $service->getApiParameters();
    }

    public function testSetApiSecret(): void
    {
        $result = $this->service->setApiSecret('new-api-secret');
        
        $this->assertSame($this->service, $result);
        $this->assertEquals('new-api-secret', $this->service->getApiSecret());
    }

    public function testSetMeasurementId(): void
    {
        $result = $this->service->setMeasurementId('G-NEW123');
        
        $this->assertSame($this->service, $result);
        $this->assertEquals('G-NEW123', $this->service->getMeasurementId());
    }

    public function testSetFirebaseId(): void
    {
        $service = new Service($this->httpClient, 'test-api-secret');
        $result = $service->setFirebaseId('test-firebase-id');
        
        $this->assertSame($service, $result);
        $this->assertEquals('test-firebase-id', $service->getFirebaseId());
    }

    public function testSetIpOverride(): void
    {
        $result = $this->service->setIpOverride('192.168.1.1');
        
        $this->assertSame($this->service, $result);
        $this->assertEquals('192.168.1.1', $this->service->getIpOverride());
        
        $params = $this->service->getApiParameters();
        $this->assertEquals('192.168.1.1', $params['uip']);
        $this->assertEquals('192.168.1.1', $params['_uip']);
    }

    public function testSetOptions(): void
    {
        $options = ['timeout' => 30, 'verify' => false];
        $result = $this->service->setOptions($options);
        
        $this->assertSame($this->service, $result);
        $this->assertEquals($options, $this->service->getOptions());
    }
}