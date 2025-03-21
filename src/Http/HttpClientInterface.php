<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Http;

use Psr\Http\Message\ResponseInterface;

interface HttpClientInterface
{
    /**
     * Send a GET request to the specified URL.
     *
     * @param string $url The URL to request
     *
     * @return ResponseInterface|null The response or null on failure
     */
    public function get(string $url): ?ResponseInterface;
}
