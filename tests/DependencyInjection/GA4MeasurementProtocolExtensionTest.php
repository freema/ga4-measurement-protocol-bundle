<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\DependencyInjection;

use Freema\GA4MeasurementProtocolBundle\Client\AnalyticsRegistry;
use Freema\GA4MeasurementProtocolBundle\Client\AnalyticsRegistryInterface;
use Freema\GA4MeasurementProtocolBundle\DataCollector\GaRequestCollector;
use Freema\GA4MeasurementProtocolBundle\DependencyInjection\GA4MeasurementProtocolExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GA4MeasurementProtocolExtensionTest extends TestCase
{
    private const CONFIG = [
        'clients' => [
            'front' => [
                'tracking_id' => 'G-TEST123',
                'api_secret' => 'test-secret',
            ],
        ],
    ];

    public function testLoadRegistersTheRegistry(): void
    {
        $container = new ContainerBuilder();

        (new GA4MeasurementProtocolExtension())->load([self::CONFIG], $container);

        $this->assertTrue($container->hasDefinition(AnalyticsRegistry::class));
        $this->assertTrue($container->hasAlias(AnalyticsRegistryInterface::class));
        $this->assertSame(['front'], array_keys($container->getDefinition(AnalyticsRegistry::class)->getArgument(0)));
    }

    public function testLoadRegistersTheDataCollectorInDebugMode(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        (new GA4MeasurementProtocolExtension())->load([self::CONFIG], $container);

        $this->assertTrue($container->hasDefinition(GaRequestCollector::class));
        $this->assertTrue($container->getDefinition(GaRequestCollector::class)->hasTag('data_collector'));
    }

    public function testLoadSkipsTheDataCollectorOutsideDebugMode(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', false);

        (new GA4MeasurementProtocolExtension())->load([self::CONFIG], $container);

        $this->assertFalse($container->hasDefinition(GaRequestCollector::class));
    }
}
