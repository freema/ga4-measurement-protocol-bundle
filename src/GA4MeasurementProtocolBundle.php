<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle;

use Freema\GA4MeasurementProtocolBundle\DependencyInjection\GA4MeasurementProtocolExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GA4MeasurementProtocolBundle extends Bundle
{
    public function getContainerExtension(): ?\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new GA4MeasurementProtocolExtension();
        }

        return $this->extension;
    }

    protected function createContainerExtension(): ?\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new GA4MeasurementProtocolExtension();
    }
}
