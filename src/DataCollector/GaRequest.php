<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\DataCollector;

class GaRequest
{
    public function __construct(private string $requestUri)
    {
    }

    public function getRequestUri(): string
    {
        return $this->requestUri;
    }
}
