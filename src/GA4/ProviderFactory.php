<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\GA4;

use Freema\GA4MeasurementProtocolBundle\Http\HttpClientFactoryInterface;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomClientIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomUserIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\DefaultClientIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\DefaultSessionIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\ProviderClientConfig;
use Freema\GA4MeasurementProtocolBundle\Provider\SessionIdHandler;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProviderFactory
{
    private RequestStack $requestStack;
    private EventDispatcherInterface $eventDispatcher;
    private LoggerInterface $logger;
    private HttpClientFactoryInterface $httpClientFactory;
    private array $httpClientConfig;
    private DefaultClientIdHandler $defaultClientIdHandler;
    private DefaultSessionIdHandler $defaultSessionIdHandler;

    public function __construct(
        RequestStack $requestStack,
        EventDispatcherInterface $eventDispatcher,
        HttpClientFactoryInterface $httpClientFactory,
        DefaultClientIdHandler $defaultClientIdHandler,
        DefaultSessionIdHandler $defaultSessionIdHandler,
        array $httpClientConfig = [],
        ?LoggerInterface $logger = null,
    ) {
        $this->requestStack = $requestStack;
        $this->eventDispatcher = $eventDispatcher;
        $this->httpClientFactory = $httpClientFactory;
        $this->httpClientConfig = $httpClientConfig;
        $this->logger = $logger ?? new NullLogger();
        $this->defaultClientIdHandler = $defaultClientIdHandler;
        $this->defaultSessionIdHandler = $defaultSessionIdHandler;
    }

    public function create(ProviderClientConfig $config): AnalyticsGA4
    {
        // Create dependencies
        $productBuilder = new ProductParameterBuilder();
        $parameterBuilder = new ParameterBuilder($productBuilder, $this->requestStack);

        try {
            // Create HTTP client
            $httpClient = $this->httpClientFactory->createHttpClient($this->httpClientConfig);

            // Create the analytics object
            $analytics = new AnalyticsGA4(
                $parameterBuilder,
                $httpClient,
                $this->eventDispatcher,
                $this->logger
            );

            // Configure the analytics object
            $analytics->setTrackingId($config->getTrackingId());

            // Client ID handling logic
            if (null !== $config->getClientId()) {
                // Use explicit client ID from config if provided
                $analytics->setClientId($config->getClientId());
            } elseif ($config->getCustomClientIdHandler() instanceof CustomClientIdHandler) {
                // Use custom handler if provided
                $customClientId = $config->getCustomClientIdHandler()->buildClientId();
                if (null !== $customClientId) {
                    $analytics->setClientId($customClientId);
                } else {
                    // Fall back to default handler if custom handler returns null
                    $defaultClientId = $this->defaultClientIdHandler->buildClientId();
                    $analytics->setClientId($defaultClientId ?? '555');
                }
            } else {
                // Use default handler if no custom handler provided
                $defaultClientId = $this->defaultClientIdHandler->buildClientId();
                $analytics->setClientId($defaultClientId ?? '555');
            }

            // User ID handling
            if ($config->getCustomUserIdHandler() instanceof CustomUserIdHandler) {
                $customUserId = $config->getCustomUserIdHandler()->buildUserId();
                if (null !== $customUserId) {
                    $analytics->setUserId($customUserId);
                }
            }

            // Session ID handling logic
            if ($config->getCustomSessionIdHandler() instanceof SessionIdHandler) {
                // Use custom handler if provided
                $customSessionId = $config->getCustomSessionIdHandler()->buildSessionId();
                if (null !== $customSessionId) {
                    $analytics->setSessionId($customSessionId);
                } else {
                    // Fall back to default handler if custom handler returns null
                    $defaultSessionId = $this->defaultSessionIdHandler->buildSessionId();
                    if ($defaultSessionId) {
                        $analytics->setSessionId($defaultSessionId);
                    }
                }
            } else {
                // Use default handler if no custom handler provided
                $defaultSessionId = $this->defaultSessionIdHandler->buildSessionId();
                if ($defaultSessionId) {
                    $analytics->setSessionId($defaultSessionId);
                }
            }

            // Set the user agent
            $request = $this->requestStack->getMainRequest();
            if ($request) {
                $ua = $request->headers->get('User-Agent', '');
                if (null !== $ua) {
                    $analytics->setUserAgentOverride($ua);
                }

                // Set document referrer
                $referrer = $request->headers->get('Referer');
                if ($referrer) {
                    $analytics->setDocumentReferrer($referrer);
                }
            }

            return $analytics;
        } catch (\Throwable $e) {
            $this->logger->error('Error creating GA4 analytics instance', [
                'exception' => $e,
                'message' => $e->getMessage(),
                'trackingId' => $config->getTrackingId(),
            ]);

            throw new \RuntimeException('Failed to create GA4 analytics instance: '.$e->getMessage(), 0, $e);
        }
    }
}
