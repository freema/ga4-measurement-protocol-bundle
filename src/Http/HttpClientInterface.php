<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface for HTTP clients.
 */
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
    
    /**
     * Send a POST request to the specified URL with JSON payload.
     *
     * @param string $url The URL to request
     * @param array $data The data to send as JSON
     * @param array $options Additional options for the request
     *
     * @return ResponseInterface|null The response or null on failure
     */
    public function post(string $url, array $data, array $options = []): ?ResponseInterface;
}