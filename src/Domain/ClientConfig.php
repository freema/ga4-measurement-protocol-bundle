<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Domain;

use Freema\GA4MeasurementProtocolBundle\Provider\CustomClientIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomSessionIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomUserIdHandler;

/**
 * Configuration object for a GA4 client.
 * Represents all the configuration needed for a single GA4 tracking setup.
 */
final class ClientConfig
{
    private readonly string $trackingId;
    private readonly string $apiSecret;
    private readonly ?string $clientId;
    private readonly bool $debugMode;
    private ?array $proxyConfig = null;

    public function __construct(
        array $config,
        private readonly ?CustomClientIdHandler $customClientIdHandler = null,
        private readonly ?CustomUserIdHandler $customUserIdHandler = null,
        private readonly ?CustomSessionIdHandler $customSessionIdHandler = null,
    ) {
        $this->trackingId = $config['tracking_id'];
        $this->apiSecret = $config['api_secret'] ?? '';
        $this->clientId = $config['client_id'] ?? null;
        $this->debugMode = $config['debug_mode'] ?? false;

        if (isset($config['proxy'])) {
            $this->proxyConfig = $config['proxy'];
        }
    }

    /**
     * Get the GA4 tracking ID (measurement ID).
     */
    public function getTrackingId(): string
    {
        return $this->trackingId;
    }

    /**
     * Get the GA4 API secret.
     */
    public function getApiSecret(): string
    {
        return $this->apiSecret;
    }

    /**
     * Get the static client ID if configured.
     */
    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    /**
     * Check if debug mode is enabled.
     */
    public function isDebugMode(): bool
    {
        return $this->debugMode;
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
    public function getCustomSessionIdHandler(): ?CustomSessionIdHandler
    {
        return $this->customSessionIdHandler;
    }

    /**
     * Get the proxy configuration.
     */
    public function getProxyConfig(): ?array
    {
        return $this->proxyConfig;
    }

    /**
     * Check if the configuration is valid.
     *
     * @return bool True if the configuration is valid
     */
    public function isValid(): bool
    {
        return !empty($this->trackingId) && !empty($this->apiSecret);
    }

    /**
     * Convert the object to an array.
     */
    public function toArray(): array
    {
        return [
            'tracking_id' => $this->trackingId,
            'api_secret' => $this->apiSecret,
            'client_id' => $this->clientId,
            'debug_mode' => $this->debugMode,
            'proxy' => $this->proxyConfig,
            'has_client_id_handler' => null !== $this->customClientIdHandler,
            'has_user_id_handler' => null !== $this->customUserIdHandler,
            'has_session_id_handler' => null !== $this->customSessionIdHandler,
        ];
    }
}
