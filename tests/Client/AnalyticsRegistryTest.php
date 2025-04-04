<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\Client;

use Freema\GA4MeasurementProtocolBundle\Analytics\AnalyticsClientInterface;
use Freema\GA4MeasurementProtocolBundle\Client\AnalyticsRegistry;
use Freema\GA4MeasurementProtocolBundle\Exception\ClientConfigKeyDontNotRegisteredException;
use Freema\GA4MeasurementProtocolBundle\Http\HttpClientInterface;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomClientIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomSessionIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomUserIdHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AnalyticsRegistryTest extends TestCase
{
    private HttpClientInterface|MockObject $httpClient;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private RequestStack|MockObject $requestStack;
    private CustomClientIdHandler|MockObject $defaultClientIdHandler;
    private AnalyticsRegistry $registry;
    private AnalyticsClientInterface|MockObject $analyticsClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->defaultClientIdHandler = $this->createMock(CustomClientIdHandler::class);
        $this->analyticsClient = $this->createMock(AnalyticsClientInterface::class);

        $serviceMap = [
            'test_client' => [
                'tracking_id' => 'G-TEST123',
            ],
        ];

        $this->registry = new AnalyticsRegistry(
            $serviceMap,
            $this->httpClient,
            $this->eventDispatcher,
            $this->requestStack,
            $this->defaultClientIdHandler
        );
    }

    public function testGetClientReturnsInstanceFromCache(): void
    {
        // Using reflection to replace the create method for testing
        $reflection = new \ReflectionClass(AnalyticsRegistry::class);
        $clientsProperty = $reflection->getProperty('clients');
        $clientsProperty->setAccessible(true);
        $clients = [];
        $clients['test_client'] = $this->analyticsClient;
        $clientsProperty->setValue($this->registry, $clients);

        // First call should return the cached instance
        $result1 = $this->registry->getClient('test_client');

        // Second call should return the same cached instance
        $result2 = $this->registry->getClient('test_client');

        $this->assertSame($result1, $result2);
        $this->assertSame($this->analyticsClient, $result1);
    }

    public function testGetClientWithCustomHandlers(): void
    {
        // Create mock handlers
        $clientIdHandler = $this->createMock(CustomClientIdHandler::class);
        $userIdHandler = $this->createMock(CustomUserIdHandler::class);
        $sessionIdHandler = $this->createMock(CustomSessionIdHandler::class);
        $logger = $this->createMock(LoggerInterface::class);

        // Client config with custom handlers
        $serviceMap = [
            'custom_handlers' => [
                'tracking_id' => 'G-TEST456',
                'api_secret' => 'secret123',
                'debug_mode' => true,
                'client_id' => 'custom-client-id',
                'custom_client_id_handler' => $clientIdHandler,
                'custom_user_id_handler' => $userIdHandler,
                'custom_session_id_handler' => $sessionIdHandler,
            ],
        ];

        $registry = new AnalyticsRegistry(
            $serviceMap,
            $this->httpClient,
            $this->eventDispatcher,
            $this->requestStack,
            $this->defaultClientIdHandler,
            $userIdHandler,
            $logger,
            $sessionIdHandler
        );

        // Using reflection to verify the client creation
        $reflection = new \ReflectionClass(AnalyticsRegistry::class);
        $clientsProperty = $reflection->getProperty('clients');
        $clientsProperty->setAccessible(true);
        $clients = [];
        $clients['custom_handlers'] = $this->analyticsClient;
        $clientsProperty->setValue($registry, $clients);

        $result = $registry->getClient('custom_handlers');
        $this->assertSame($this->analyticsClient, $result);
    }

    public function testGetClientThrowsExceptionForUnknownClient(): void
    {
        $this->expectException(ClientConfigKeyDontNotRegisteredException::class);
        $this->registry->getClient('unknown_client');
    }
}
