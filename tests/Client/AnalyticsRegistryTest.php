<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\Client;

use Freema\GA4MeasurementProtocolBundle\Client\AnalyticsRegistry;
use Freema\GA4MeasurementProtocolBundle\Exception\ClientConfigKeyDontNotRegisteredException;
use Freema\GA4MeasurementProtocolBundle\GA4\AnalyticsGA4;
use Freema\GA4MeasurementProtocolBundle\GA4\ProviderFactory;
use Freema\GA4MeasurementProtocolBundle\Http\HttpClientFactoryInterface;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomClientIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomUserIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\ProviderClientConfig;
use Freema\GA4MeasurementProtocolBundle\Provider\SessionIdHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AnalyticsRegistryTest extends TestCase
{
    private ProviderFactory|MockObject $providerFactory;
    private HttpClientFactoryInterface|MockObject $httpClientFactory;
    private AnalyticsRegistry $registry;
    private AnalyticsGA4|MockObject $analytics;

    protected function setUp(): void
    {
        $this->providerFactory = $this->createMock(ProviderFactory::class);
        $this->httpClientFactory = $this->createMock(HttpClientFactoryInterface::class);
        $this->analytics = $this->createMock(AnalyticsGA4::class);

        $clientConfig = [
            'test_client' => [
                'tracking_id' => 'G-TEST123',
                'secret_key' => 'test-secret',
            ],
        ];

        $this->registry = new AnalyticsRegistry($clientConfig, $this->providerFactory, $this->httpClientFactory);
    }

    public function testGetAnalyticsReturnsInstanceFromCache(): void
    {
        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->analytics);

        // First call should create a new instance
        $result1 = $this->registry->getAnalytics('test_client');

        // Second call should return the cached instance
        $result2 = $this->registry->getAnalytics('test_client');

        $this->assertSame($result1, $result2);
        $this->assertSame($this->analytics, $result1);
    }

    public function testGetAnalyticsWithCustomHandlers(): void
    {
        // Create mock handlers
        $clientIdHandler = $this->createMock(CustomClientIdHandler::class);
        $userIdHandler = $this->createMock(CustomUserIdHandler::class);
        $sessionIdHandler = $this->createMock(SessionIdHandler::class);

        // Client config with custom handlers
        $clientConfig = [
            'custom_handlers' => [
                'tracking_id' => 'G-TEST456',
                'custom_client_id_handler' => $clientIdHandler,
                'custom_user_id_handler' => $userIdHandler,
                'custom_session_id_handler' => $sessionIdHandler,
            ],
        ];

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (ProviderClientConfig $config) use ($clientIdHandler, $userIdHandler, $sessionIdHandler) {
                $this->assertEquals('G-TEST456', $config->getTrackingId());
                $this->assertSame($clientIdHandler, $config->getCustomClientIdHandler());
                $this->assertSame($userIdHandler, $config->getCustomUserIdHandler());
                $this->assertSame($sessionIdHandler, $config->getCustomSessionIdHandler());

                return true;
            }))
            ->willReturn($this->analytics);

        $registry = new AnalyticsRegistry($clientConfig, $this->providerFactory, $this->httpClientFactory);
        $result = $registry->getAnalytics('custom_handlers');

        $this->assertSame($this->analytics, $result);
    }

    public function testGetAnalyticsThrowsExceptionForUnknownClient(): void
    {
        $this->expectException(ClientConfigKeyDontNotRegisteredException::class);
        $this->registry->getAnalytics('unknown_client');
    }
}
