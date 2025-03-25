<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\DataCollector;

/**
 * Represents a GA4 Measurement Protocol request for data collection.
 */
class GaRequest
{
    /**
     * @var array
     */
    private array $payload;

    /**
     * @param string $requestUri The request URI (URL)
     * @param array $payload The request payload (body)
     */
    public function __construct(
        private string $requestUri,
        ?array $payload = null
    ) {
        $this->payload = $payload ?? [];
    }

    /**
     * Get the request URI.
     */
    public function getRequestUri(): string
    {
        return $this->requestUri;
    }
    
    /**
     * Get the request payload.
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
}