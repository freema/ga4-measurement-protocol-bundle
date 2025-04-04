<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Client;

use Freema\GA4MeasurementProtocolBundle\Analytics\AnalyticsClientInterface;

/**
 * Service for retrieving analytics clients by key.
 */
interface AnalyticsRegistryInterface
{
    /**
     * Get an analytics client by key.
     */
    public function getClient(string $key): AnalyticsClientInterface;
}
