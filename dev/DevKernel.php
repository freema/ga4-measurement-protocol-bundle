<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dev;

use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Freema\GA4MeasurementProtocolBundle\GA4MeasurementProtocolBundle;

class DevKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new DebugBundle(),
            new FrameworkBundle(),
            new GA4MeasurementProtocolBundle(),
        ];
    }

    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container): void {
            $container->loadFromExtension('framework', [
                'test' => true,
                'secret' => 'test',
                'router' => [
                    'utf8' => true,
                    'resource' => '%kernel.project_dir%/dev/config/routes.yaml',
                ],
                'http_method_override' => false,
            ]);

            // Logger configuration removed
        });

        $loader->load(__DIR__ . '/config/services.yaml');

        $loader->load(function (ContainerBuilder $container): void {
            $container->loadFromExtension('ga4_measurement_protocol', [
                'clients' => [
                    'dev' => [
                        'tracking_id' => 'G-TESTTRACK123',
                    ],
                ],
            ]);
        });
    }

    public function getCacheDir(): string
    {
        if (method_exists($this, 'getProjectDir')) {
            return $this->getProjectDir() . '/dev/cache/' . $this->getEnvironment();
        }

        return parent::getCacheDir();
    }

    public function getLogDir(): string
    {
        if (method_exists($this, 'getProjectDir')) {
            return $this->getProjectDir() . '/dev/cache/' . $this->getEnvironment();
        }

        return parent::getLogDir();
    }
}