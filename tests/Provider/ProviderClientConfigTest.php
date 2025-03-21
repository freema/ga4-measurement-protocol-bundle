<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\Provider;

use Freema\GA4MeasurementProtocolBundle\Provider\CustomClientIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomUserIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\ProviderClientConfig;
use Freema\GA4MeasurementProtocolBundle\Provider\SessionIdHandler;
use PHPUnit\Framework\TestCase;

class ProviderClientConfigTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $config = [
            'tracking_id' => 'G-TEST123',
            'client_id' => '123.456',
            'ga4_endpoint' => 'https://custom-endpoint.com/g/collect',
        ];

        $clientIdHandler = $this->createMock(CustomClientIdHandler::class);
        $userIdHandler = $this->createMock(CustomUserIdHandler::class);
        $sessionIdHandler = $this->createMock(SessionIdHandler::class);

        $providerConfig = new ProviderClientConfig(
            $config,
            $clientIdHandler,
            $userIdHandler,
            $sessionIdHandler
        );

        $this->assertEquals('G-TEST123', $providerConfig->getTrackingId());
        $this->assertEquals('123.456', $providerConfig->getClientId());
        $this->assertEquals('https://custom-endpoint.com/g/collect', $providerConfig->getGa4Endpoint());
        $this->assertSame($clientIdHandler, $providerConfig->getCustomClientIdHandler());
        $this->assertSame($userIdHandler, $providerConfig->getCustomUserIdHandler());
        $this->assertSame($sessionIdHandler, $providerConfig->getCustomSessionIdHandler());
    }

    public function testConstructorWithMinimalConfig(): void
    {
        $config = [
            'tracking_id' => 'G-TEST123',
        ];

        $providerConfig = new ProviderClientConfig(
            $config,
            null,
            null,
            null
        );

        $this->assertEquals('G-TEST123', $providerConfig->getTrackingId());
        $this->assertNull($providerConfig->getClientId());
        $this->assertNull($providerConfig->getGa4Endpoint());
        $this->assertNull($providerConfig->getCustomClientIdHandler());
        $this->assertNull($providerConfig->getCustomUserIdHandler());
        $this->assertNull($providerConfig->getCustomSessionIdHandler());
    }

    public function testHandlesMissingEndpoint(): void
    {
        $config = [
            'tracking_id' => 'G-TEST123',
            'ga4_endpoint' => '',  // Empty string
        ];

        $providerConfig = new ProviderClientConfig(
            $config,
            null,
            null,
            null
        );

        $this->assertNull($providerConfig->getGa4Endpoint());
    }
}
