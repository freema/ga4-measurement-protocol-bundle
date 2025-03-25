<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Client;

use Freema\GA4MeasurementProtocolBundle\GA4\AnalyticsGA4;
use Freema\GA4MeasurementProtocolBundle\GA4\Service;
use Freema\GA4MeasurementProtocolBundle\Provider\ProviderClientConfig;

interface AnalyticsRegistryInterface
{
    /**
     * Get a GA4 analytics instance by key.
     */
    public function getAnalytics(string $key): AnalyticsGA4;
    
    /**
     * Get a GA4 service instance by key.
     */
    public function getService(string $key): Service;
    
    /**
     * Get a client configuration by key.
     */
    public function getClientConfig(string $key): ?ProviderClientConfig;
}