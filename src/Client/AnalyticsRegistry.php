<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Client;

use Freema\GA4MeasurementProtocolBundle\Analytics\AnalyticsClient;
use Freema\GA4MeasurementProtocolBundle\Analytics\AnalyticsClientInterface;
use Freema\GA4MeasurementProtocolBundle\Exception\ClientConfigKeyDontNotRegisteredException;
use Freema\GA4MeasurementProtocolBundle\Http\HttpClientInterface;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomClientIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomSessionIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomUserIdHandler;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class AnalyticsRegistry implements AnalyticsRegistryInterface
{
    /** @var array<string, AnalyticsClientInterface> */
    private array $clients = [];

    /**
     * AnalyticsRegistry constructor.
     */
    public function __construct(
        private readonly array $serviceMap,
        private readonly HttpClientInterface $httpClient,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RequestStack $requestStack,
        private readonly CustomClientIdHandler $defaultClientIdHandler,
        private readonly ?CustomUserIdHandler $defaultUserIdHandler = null,
        private readonly ?LoggerInterface $logger = null,
        private readonly ?CustomSessionIdHandler $defaultSessionIdHandler = null,
    ) {
    }

    /**
     * Get a client by key.
     *
     * @throws ClientConfigKeyDontNotRegisteredException
     */
    public function getClient(string $key): AnalyticsClientInterface
    {
        // Return cached instance if available
        if (isset($this->clients[$key])) {
            return $this->clients[$key];
        }

        if (!isset($this->serviceMap[$key])) {
            throw new ClientConfigKeyDontNotRegisteredException(sprintf('Client key "%s" is not registered', $key));
        }

        $config = $this->serviceMap[$key];

        // Get or create the client ID handler
        $clientIdHandler = isset($config['custom_client_id_handler'])
                           && $config['custom_client_id_handler'] instanceof CustomClientIdHandler
            ? $config['custom_client_id_handler']
            : $this->defaultClientIdHandler;

        // Get the user ID handler if configured
        $userIdHandler = isset($config['custom_user_id_handler'])
                         && $config['custom_user_id_handler'] instanceof CustomUserIdHandler
            ? $config['custom_user_id_handler']
            : $this->defaultUserIdHandler;

        // Get the session ID handler if configured
        $sessionIdHandler = isset($config['custom_session_id_handler'])
                           && $config['custom_session_id_handler'] instanceof CustomSessionIdHandler
            ? $config['custom_session_id_handler']
            : $this->defaultSessionIdHandler;

        // Create the client
        $client = new AnalyticsClient(
            $this->httpClient,
            $this->eventDispatcher,
            $this->requestStack,
            $clientIdHandler,
            $userIdHandler,
            $sessionIdHandler,
            $this->logger ?? new NullLogger(),
            $config['tracking_id'] ?? '',
            $config['api_secret'] ?? ''
        );

        // Set client ID if provided
        if (isset($config['client_id'])) {
            $client->setClientId($config['client_id']);
        }

        // Set debug mode if configured
        if (isset($config['debug_mode']) && $config['debug_mode']) {
            $client->setDebugMode(true);
        }

        // Cache and return
        return $this->clients[$key] = $client;
    }
}
