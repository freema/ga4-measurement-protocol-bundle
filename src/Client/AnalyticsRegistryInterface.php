<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Client;

use Freema\GA4MeasurementProtocolBundle\GA4\AnalyticsGA4;

interface AnalyticsRegistryInterface
{
    public function getAnalytics(string $key): AnalyticsGA4;
}
