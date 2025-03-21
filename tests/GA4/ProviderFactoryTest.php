<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\GA4;

use Freema\GA4MeasurementProtocolBundle\GA4\AnalyticsGA4;
use Freema\GA4MeasurementProtocolBundle\GA4\ProviderFactory;
use Freema\GA4MeasurementProtocolBundle\Http\HttpClientFactoryInterface;
use Freema\GA4MeasurementProtocolBundle\Http\HttpClientInterface;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomClientIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomUserIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\DefaultClientIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\DefaultSessionIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\ProviderClientConfig;
use Freema\GA4MeasurementProtocolBundle\Provider\SessionIdHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProviderFactoryTest extends TestCase
{
    private RequestStack|MockObject $requestStack;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private HttpClientFactoryInterface|MockObject $httpClientFactory;
    private DefaultClientIdHandler|MockObject $defaultClientIdHandler;
    private DefaultSessionIdHandler|MockObject $defaultSessionIdHandler;
    private LoggerInterface|MockObject $logger;
    private ProviderFactory $factory;

    protected function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->httpClientFactory = $this->createMock(HttpClientFactoryInterface::class);
        $this->defaultClientIdHandler = $this->createMock(DefaultClientIdHandler::class);
        $this->defaultSessionIdHandler = $this->createMock(DefaultSessionIdHandler::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->factory = new ProviderFactory(
            $this->requestStack,
            $this->eventDispatcher,
            $this->httpClientFactory,
            $this->defaultClientIdHandler,
            $this->defaultSessionIdHandler,
            [],
            $this->logger
        );
    }

    public function testCreateWithExplicitClientId(): void
    {
        $config = new ProviderClientConfig(
            [
                'tracking_id' => 'G-TEST123',
                'client_id' => 'explicit-client-id',
            ],
            null,
            null,
            null
        );

        $httpClient = $this->createMock(HttpClientInterface::class);
        $this->httpClientFactory->expects($this->once())
            ->method('createHttpClient')
            ->willReturn($httpClient);

        $analytics = $this->factory->create($config);

        $this->assertInstanceOf(AnalyticsGA4::class, $analytics);
    }

    public function testCreateWithCustomClientIdHandler(): void
    {
        $customClientIdHandler = $this->createMock(CustomClientIdHandler::class);
        $customClientIdHandler->expects($this->once())
            ->method('buildClientId')
            ->willReturn('custom-client-id');

        $config = new ProviderClientConfig(
            [
                'tracking_id' => 'G-TEST123',
            ],
            $customClientIdHandler,
            null,
            null
        );

        $httpClient = $this->createMock(HttpClientInterface::class);
        $this->httpClientFactory->expects($this->once())
            ->method('createHttpClient')
            ->willReturn($httpClient);

        $analytics = $this->factory->create($config);

        $this->assertInstanceOf(AnalyticsGA4::class, $analytics);
    }

    public function testCreateWithDefaultClientIdHandler(): void
    {
        $config = new ProviderClientConfig(
            [
                'tracking_id' => 'G-TEST123',
            ],
            null,
            null,
            null
        );

        $this->defaultClientIdHandler->expects($this->once())
            ->method('buildClientId')
            ->willReturn('default-client-id');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $this->httpClientFactory->expects($this->once())
            ->method('createHttpClient')
            ->willReturn($httpClient);

        $analytics = $this->factory->create($config);

        $this->assertInstanceOf(AnalyticsGA4::class, $analytics);
    }

    public function testCreateSetsUserIdFromCustomHandler(): void
    {
        $customUserIdHandler = $this->createMock(CustomUserIdHandler::class);
        $customUserIdHandler->expects($this->once())
            ->method('buildUserId')
            ->willReturn('custom-user-id');

        $config = new ProviderClientConfig(
            [
                'tracking_id' => 'G-TEST123',
            ],
            null,
            $customUserIdHandler,
            null
        );

        $httpClient = $this->createMock(HttpClientInterface::class);
        $this->httpClientFactory->expects($this->once())
            ->method('createHttpClient')
            ->willReturn($httpClient);

        $analytics = $this->factory->create($config);

        $this->assertInstanceOf(AnalyticsGA4::class, $analytics);
    }

    public function testCreateSetsSessionIdFromHandler(): void
    {
        $sessionIdHandler = $this->createMock(SessionIdHandler::class);
        $sessionIdHandler->expects($this->once())
            ->method('buildSessionId')
            ->willReturn('custom-session-id');

        $config = new ProviderClientConfig(
            [
                'tracking_id' => 'G-TEST123',
            ],
            null,
            null,
            $sessionIdHandler
        );

        $httpClient = $this->createMock(HttpClientInterface::class);
        $this->httpClientFactory->expects($this->once())
            ->method('createHttpClient')
            ->willReturn($httpClient);

        $analytics = $this->factory->create($config);

        $this->assertInstanceOf(AnalyticsGA4::class, $analytics);
    }

    public function testCreateSetsUserAgentAndReferrer(): void
    {
        $request = $this->createMock(Request::class);
        $headers = $this->createMock(HeaderBag::class);
        $request->headers = $headers;

        $headers->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['User-Agent', '', 'Test User Agent'],
                ['Referer', null, 'https://referrer.com'],
            ]);

        $this->requestStack->expects($this->once())
            ->method('getMainRequest')
            ->willReturn($request);

        $config = new ProviderClientConfig(
            [
                'tracking_id' => 'G-TEST123',
            ],
            null,
            null,
            null
        );

        $httpClient = $this->createMock(HttpClientInterface::class);
        $this->httpClientFactory->expects($this->once())
            ->method('createHttpClient')
            ->willReturn($httpClient);

        $this->defaultClientIdHandler->method('buildClientId')
            ->willReturn('default-client-id');

        $this->defaultSessionIdHandler->method('buildSessionId')
            ->willReturn('default-session-id');

        $analytics = $this->factory->create($config);

        $this->assertInstanceOf(AnalyticsGA4::class, $analytics);
    }

    public function testCreateHandlesExceptionAndRethrows(): void
    {
        $config = new ProviderClientConfig(
            [
                'tracking_id' => 'G-TEST123',
            ],
            null,
            null,
            null
        );

        $this->httpClientFactory->expects($this->once())
            ->method('createHttpClient')
            ->willThrowException(new \RuntimeException('Test exception'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Error creating GA4 analytics instance', $this->callback(function ($context) {
                return isset($context['exception'])
                    && isset($context['message'])
                    && 'Test exception' === $context['message']
                    && 'G-TEST123' === $context['trackingId'];
            }));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to create GA4 analytics instance: Test exception');

        $this->factory->create($config);
    }
}
