<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\GA4;

use Freema\GA4MeasurementProtocolBundle\DataCollector\GaRequest;
use Freema\GA4MeasurementProtocolBundle\GA4\AnalyticsGA4;
use Freema\GA4MeasurementProtocolBundle\GA4\ParameterBuilder;
use Freema\GA4MeasurementProtocolBundle\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AnalyticsGA4Test extends TestCase
{
    private ParameterBuilder|MockObject $parameterBuilder;
    private HttpClientInterface|MockObject $httpClient;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private LoggerInterface|MockObject $logger;
    private AnalyticsGA4 $analytics;

    protected function setUp(): void
    {
        $this->parameterBuilder = $this->createMock(ParameterBuilder::class);
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->analytics = new AnalyticsGA4(
            $this->parameterBuilder,
            $this->httpClient,
            $this->eventDispatcher,
            $this->logger
        );

        $this->analytics->setTrackingId('G-TEST123');
    }

    public function testSendPageview(): void
    {
        $testUrl = 'https://analytics.google.com/g/collect?v=2&tid=G-TEST123&t=pageview';

        $this->parameterBuilder
            ->expects($this->once())
            ->method('buildParameters')
            ->willReturn([
                'params' => [
                    'v' => '2',
                    'tid' => 'G-TEST123',
                    't' => 'pageview',
                ],
                'url' => $testUrl,
            ]);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (GaRequest $request) use ($testUrl) {
                return $request->getRequestUri() === $testUrl;
            }));

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with($testUrl);

        $result = $this->analytics->sendPageview();

        $this->assertEquals($testUrl, $result);
    }

    public function testSendEvent(): void
    {
        $testUrl = 'https://analytics.google.com/g/collect?v=2&tid=G-TEST123&t=event&en=purchase';

        $this->analytics->setEventName('purchase');

        $this->parameterBuilder
            ->expects($this->once())
            ->method('buildParameters')
            ->willReturn([
                'params' => [
                    'v' => '2',
                    'tid' => 'G-TEST123',
                    't' => 'event',
                    'en' => 'purchase',
                ],
                'url' => $testUrl,
            ]);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (GaRequest $request) use ($testUrl) {
                return $request->getRequestUri() === $testUrl;
            }));

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with($testUrl);

        $result = $this->analytics->sendEvent();

        $this->assertEquals($testUrl, $result);
    }

    public function testSettersReturnSelf(): void
    {
        $result = $this->analytics
            ->setClientId('123.456')
            ->setUserId('user123')
            ->setDocumentPath('/test')
            ->setDocumentTitle('Test Page')
            ->setCurrency('USD')
            ->setTransactionId('TX-123')
            ->setRevenue(99.99)
            ->setTax(10.0)
            ->setShipping(5.0)
            ->setDiscount(2.0)
            ->setAffiliation('Test Store')
            ->setPaymentType('Credit Card')
            ->setShippingTier('Standard')
            ->addProduct(['sku' => 'TEST', 'name' => 'Test Product'])
            ->setEventCategory('purchase')
            ->setEventAction('complete')
            ->setEventName('purchase')
            ->addCustomParameter('custom_key', 'custom_value')
            ->setCustomEndpoint('https://custom.endpoint.com');

        $this->assertSame($this->analytics, $result);
    }

    public function testHandlesExceptionDuringRequest(): void
    {
        $testUrl = 'https://analytics.google.com/g/collect?v=2&tid=G-TEST123&t=pageview';
        $exception = new \Exception('Connection error');

        $this->parameterBuilder
            ->expects($this->once())
            ->method('buildParameters')
            ->willReturn([
                'params' => ['v' => '2', 'tid' => 'G-TEST123', 't' => 'pageview'],
                'url' => $testUrl,
            ]);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Error sending GA4 request',
                $this->callback(function (array $context) use ($exception) {
                    return isset($context['error']) && $context['exception'] === $exception;
                })
            );

        $result = $this->analytics->sendPageview();

        $this->assertEquals($testUrl, $result);
    }
}
