<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\DependencyInjection;

use Freema\GA4MeasurementProtocolBundle\DependencyInjection\GA4MeasurementProtocolExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ProxyConfigurationTest extends TestCase
{
    private const CLIENTS = ['front' => ['tracking_id' => 'G-TEST123', 'api_secret' => 'test-secret']];

    public function testTopLevelProxyReachesTheHttpClient(): void
    {
        $options = $this->httpClientOptions([
            'proxy' => 'http://proxy.example.com:3128',
            'no_proxy' => ['localhost'],
            'clients' => self::CLIENTS,
        ]);

        $this->assertSame('http://proxy.example.com:3128', $options['proxy']);
        $this->assertSame(['localhost'], $options['no_proxy']);
    }

    public function testHttpClientConfigWinsOverTheTopLevelProxy(): void
    {
        $options = $this->httpClientOptions([
            'proxy' => 'http://top-level.example.com:3128',
            'http_client' => ['config' => ['proxy' => 'http://http-client.example.com:3128']],
            'clients' => self::CLIENTS,
        ]);

        $this->assertSame('http://http-client.example.com:3128', $options['proxy']);
    }

    public function testNoProxyByDefault(): void
    {
        $this->assertArrayNotHasKey('proxy', $this->httpClientOptions(['clients' => self::CLIENTS]));
    }

    private function httpClientOptions(array $config): array
    {
        $container = new ContainerBuilder();
        (new GA4MeasurementProtocolExtension())->load([$config], $container);

        return $container->getDefinition('ga4_measurement_protocol.http_client')->getArgument(0);
    }
}
