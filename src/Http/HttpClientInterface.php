<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Http;

use Symfony\Contracts\HttpClient\ResponseInterface;

interface HttpClientInterface
{
    /**
     * Send a GA4 request to the Measurement Protocol API.
     *
     * @param string $measurementId GA4 Measurement ID
     * @param string $apiSecret     API Secret
     * @param array  $payload       Request payload
     * @param bool   $debug         Whether to use debug endpoint
     *
     * @return ResponseInterface HTTP client response
     */
    public function sendGA4Request(string $measurementId, string $apiSecret, array $payload, bool $debug = false): ResponseInterface;
}
