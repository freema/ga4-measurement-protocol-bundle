<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Provider;

final class ProviderClientConfig
{
    private readonly string $trackingId;
    private readonly ?string $clientId;
    private readonly ?string $ga4Endpoint;

    public function __construct(
        array $config,
        private readonly ?CustomClientIdHandler $customClientIdHandler,
        private readonly ?CustomUserIdHandler $customUserIdHandler,
        private readonly ?SessionIdHandler $customSessionIdHandler = null,
    ) {
        $this->trackingId = $config['tracking_id'];
        $this->clientId = $config['client_id'] ?? null;
        $this->ga4Endpoint = !empty($config['ga4_endpoint']) ? $config['ga4_endpoint'] : null;
    }

    public function getTrackingId(): string
    {
        return $this->trackingId;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function getGa4Endpoint(): ?string
    {
        return $this->ga4Endpoint;
    }

    public function getCustomClientIdHandler(): ?CustomClientIdHandler
    {
        return $this->customClientIdHandler;
    }

    public function getCustomUserIdHandler(): ?CustomUserIdHandler
    {
        return $this->customUserIdHandler;
    }

    public function getCustomSessionIdHandler(): ?SessionIdHandler
    {
        return $this->customSessionIdHandler;
    }
}
