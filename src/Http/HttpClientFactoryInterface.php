<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Http;

interface HttpClientFactoryInterface
{
    /**
     * Create an HTTP client implementation.
     *
     * @param array $config Configuration options
     */
    public function createHttpClient(array $config = []): HttpClientInterface;
}
