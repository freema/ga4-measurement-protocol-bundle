<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Client;

use Freema\GA4MeasurementProtocolBundle\Exception\ClientConfigKeyDontNotRegisteredException;
use Freema\GA4MeasurementProtocolBundle\GA4\AnalyticsGA4;
use Freema\GA4MeasurementProtocolBundle\GA4\ProviderFactory;
use Freema\GA4MeasurementProtocolBundle\GA4\Service;
use Freema\GA4MeasurementProtocolBundle\Http\HttpClientFactoryInterface;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomClientIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomUserIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\ProviderClientConfig;
use Freema\GA4MeasurementProtocolBundle\Provider\SessionIdHandler;

final class AnalyticsRegistry implements AnalyticsRegistryInterface
{
    /** @var AnalyticsGA4[] */
    private array $providerMap = [];
    
    /** @var Service[] */
    private array $serviceMap = [];
    
    /** @var ProviderClientConfig[] */
    private array $configMap = [];

    /**
     * AnalyticsRegistry constructor.
     */
    public function __construct(
        private readonly array $clientConfigMap,
        private readonly ProviderFactory $providerFactory,
        private readonly HttpClientFactoryInterface $httpClientFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getAnalytics(string $key): AnalyticsGA4
    {
        // Return cached instance if available
        if (isset($this->providerMap[$key]) && ($this->providerMap[$key] instanceof AnalyticsGA4)) {
            return $this->providerMap[$key];
        }

        // Get the client config
        $config = $this->getClientConfig($key);
        if (!$config) {
            throw new ClientConfigKeyDontNotRegisteredException(sprintf('Provider with key %s does not exist or cannot be instantiated!', $key));
        }
        
        // Create provider
        $provider = $this->providerFactory->create($config);

        // Cache and return
        return $this->providerMap[$key] = $provider;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getService(string $key): Service
    {
        // Return cached instance if available
        if (isset($this->serviceMap[$key]) && ($this->serviceMap[$key] instanceof Service)) {
            return $this->serviceMap[$key];
        }

        // Get the client config
        $config = $this->getClientConfig($key);
        if (!$config) {
            throw new ClientConfigKeyDontNotRegisteredException(sprintf('Provider with key %s does not exist or cannot be instantiated!', $key));
        }
        
        // Create HTTP client
        $httpClient = $this->httpClientFactory->createHttpClient([]);
        
        // Create service
        $service = new Service(
            $httpClient,
            $config->getSecretKey(),
            $config->getTrackingId()
        );

        // Cache and return
        return $this->serviceMap[$key] = $service;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getClientConfig(string $key): ?ProviderClientConfig
    {
        // Return cached config if available
        if (isset($this->configMap[$key]) && ($this->configMap[$key] instanceof ProviderClientConfig)) {
            return $this->configMap[$key];
        }
        
        if (isset($this->clientConfigMap[$key])) {
            $map = $this->clientConfigMap[$key];

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

            // Create and cache config
            $config = new ProviderClientConfig(
                $map,
                $clientIdHandler,
                $userIdHandler,
                $sessionIdHandler
            );
            
            return $this->configMap[$key] = $config;
        }
        
        return null;
    }
}