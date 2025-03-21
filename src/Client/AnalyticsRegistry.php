<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Client;

use Freema\GA4MeasurementProtocolBundle\Exception\ClientConfigKeyDontNotRegisteredException;
use Freema\GA4MeasurementProtocolBundle\GA4\AnalyticsGA4;
use Freema\GA4MeasurementProtocolBundle\GA4\ProviderFactory;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomClientIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomUserIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\ProviderClientConfig;
use Freema\GA4MeasurementProtocolBundle\Provider\SessionIdHandler;

final class AnalyticsRegistry implements AnalyticsRegistryInterface
{
    /** @var AnalyticsGA4[] */
    private array $providerMap = [];

    /**
     * AnalyticsRegistry constructor.
     */
    public function __construct(
        private readonly array $serviceMap,
        private readonly ProviderFactory $providerFactory,
    ) {
    }

    public function getAnalytics(string $key): AnalyticsGA4
    {
        // Return cached instance if available
        if (isset($this->providerMap[$key]) && ($this->providerMap[$key] instanceof AnalyticsGA4)) {
            return $this->providerMap[$key];
        }

        if (isset($this->serviceMap[$key])) {
            $map = $this->serviceMap[$key];

            // Check and convert custom_client_id_handler
            $clientIdHandler = null;
            if (isset($map['custom_client_id_handler'])
                && $map['custom_client_id_handler'] instanceof CustomClientIdHandler) {
                $clientIdHandler = $map['custom_client_id_handler'];
            }

            // Check and convert custom_user_id_handler
            $userIdHandler = null;
            if (isset($map['custom_user_id_handler'])
                && $map['custom_user_id_handler'] instanceof CustomUserIdHandler) {
                $userIdHandler = $map['custom_user_id_handler'];
            }

            // Check and convert custom_session_id_handler
            $sessionIdHandler = null;
            if (isset($map['custom_session_id_handler'])
                && $map['custom_session_id_handler'] instanceof SessionIdHandler) {
                $sessionIdHandler = $map['custom_session_id_handler'];
            }

            // Create config and provider
            $config = new ProviderClientConfig(
                $map,
                $clientIdHandler,
                $userIdHandler,
                $sessionIdHandler
            );
            $provider = $this->providerFactory->create($config);

            // Cache and return
            return $this->providerMap[$key] = $provider;
        }

        throw new ClientConfigKeyDontNotRegisteredException(sprintf('Provider with key %s does not exist or cannot be instantiated!', $key));
    }
}
