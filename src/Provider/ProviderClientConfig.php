<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Provider;

/**
 * Configuration holder for a GA4 client.
 */
final class ProviderClientConfig
{
    private readonly string $trackingId;
    private readonly ?string $clientId;
    private readonly ?string $ga4Endpoint;
    private readonly ?string $secretKey;

    public function __construct(
        array $config,
        private readonly ?CustomClientIdHandler $customClientIdHandler,
        private readonly ?CustomUserIdHandler $customUserIdHandler,
        private readonly ?SessionIdHandler $customSessionIdHandler = null,
    ) {
        $this->trackingId = $config['tracking_id'];
        $this->clientId = $config['client_id'] ?? null;
        $this->ga4Endpoint = !empty($config['ga4_endpoint']) ? $config['ga4_endpoint'] : null;
        $this->secretKey = $config['secret_key'] ?? null;
    }

    /**
     * Get the tracking ID (measurement ID).
     */
    public function getTrackingId(): string
    {
        return $this->trackingId;
    }

    /**
     * Get the client ID.
     */
    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    /**
     * Get the GA4 endpoint.
     */
    public function getGa4Endpoint(): ?string
    {
        return $this->ga4Endpoint;
    }

    /**
     * Get the API secret key.
     */
    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    /**
     * Get the custom client ID handler.
     */
    public function getCustomClientIdHandler(): ?CustomClientIdHandler
    {
        return $this->customClientIdHandler;
    }

    /**
     * Get the custom user ID handler.
     */
    public function getCustomUserIdHandler(): ?CustomUserIdHandler
    {
        return $this->customUserIdHandler;
    }

    /**
     * Get the custom session ID handler.
     */
    public function getCustomSessionIdHandler(): ?SessionIdHandler
    {
        return $this->customSessionIdHandler;
    }
}