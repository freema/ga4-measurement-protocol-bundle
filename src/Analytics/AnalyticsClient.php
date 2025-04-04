<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Analytics;

use Freema\GA4MeasurementProtocolBundle\Domain\AnalyticsRequest;
use Freema\GA4MeasurementProtocolBundle\Domain\AnalyticsUrl;
use Freema\GA4MeasurementProtocolBundle\Event\EventInterface;
use Freema\GA4MeasurementProtocolBundle\Exception\ClientIdException;
use Freema\GA4MeasurementProtocolBundle\Exception\ValidationException;
use Freema\GA4MeasurementProtocolBundle\Http\HttpClientInterface;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomClientIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomSessionIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\CustomUserIdHandler;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Core analytics client that handles sending events to GA4.
 */
class AnalyticsClient implements AnalyticsClientInterface
{
    private string $trackingId;
    private string $apiSecret;
    private ?string $clientId = null;
    private ?string $userId = null;
    private ?string $sessionId = null;
    private bool $debugMode = false;

    /** @var EventInterface[] */
    private array $events = [];

    private array $lastSentParameters = [];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RequestStack $requestStack,
        private readonly CustomClientIdHandler $clientIdHandler,
        private readonly ?CustomUserIdHandler $userIdHandler = null,
        private readonly ?CustomSessionIdHandler $sessionIdHandler = null,
        private readonly LoggerInterface $logger = new NullLogger(),
        string $trackingId = '',
        string $apiSecret = '',
    ) {
        $this->trackingId = $trackingId;
        $this->apiSecret = $apiSecret;

        // If we have a session ID handler and tracking ID, set the tracking ID on the handler
        if (null !== $this->sessionIdHandler && $trackingId) {
            if (method_exists($this->sessionIdHandler, 'setTrackingId')) {
                $this->sessionIdHandler->setTrackingId($trackingId);
            }
        }
    }

    /**
     * Set the tracking ID.
     */
    public function setTrackingId(string $trackingId): self
    {
        $this->trackingId = $trackingId;

        // If we have a session ID handler, set the tracking ID on the handler
        if (null !== $this->sessionIdHandler) {
            if (method_exists($this->sessionIdHandler, 'setTrackingId')) {
                $this->sessionIdHandler->setTrackingId($trackingId);
            }
        }

        return $this;
    }

    /**
     * Set the API secret.
     */
    public function setApiSecret(string $apiSecret): self
    {
        $this->apiSecret = $apiSecret;

        return $this;
    }

    /**
     * Set the client ID.
     */
    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Set the user ID.
     */
    public function setUserId(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Set the session ID.
     */
    public function setSessionId(string $sessionId): self
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * Set debug mode.
     */
    public function setDebugMode(bool $debugMode): self
    {
        $this->debugMode = $debugMode;

        return $this;
    }

    /**
     * Add an event to be sent.
     */
    public function addEvent(EventInterface $event): self
    {
        // If debug mode is enabled, add it to the event parameters
        if ($this->debugMode) {
            $event->addParameter('debug_mode', '1');
        }

        $this->events[] = $event;

        return $this;
    }

    /**
     * Add an event to be sent, validating it first.
     *
     * @throws ValidationException If the event validation fails
     */
    public function addValidatedEvent(EventInterface $event): self
    {
        // Validate the event before adding it
        $event->validate();

        return $this->addEvent($event);
    }

    /**
     * Get the last sent parameters.
     */
    public function getLastSentParameters(): array
    {
        return $this->lastSentParameters;
    }

    /**
     * Get client ID handler.
     */
    public function getClientIdHandler(): CustomClientIdHandler
    {
        return $this->clientIdHandler;
    }

    /**
     * Get user ID handler.
     */
    public function getUserIdHandler(): ?CustomUserIdHandler
    {
        return $this->userIdHandler;
    }

    /**
     * Get session ID handler.
     */
    public function getSessionIdHandler(): ?CustomSessionIdHandler
    {
        return $this->sessionIdHandler;
    }

    /**
     * Is debug mode enabled.
     */
    public function isDebugMode(): bool
    {
        return $this->debugMode;
    }

    /**
     * Send all queued events to GA4.
     *
     * @param bool $validate Whether to validate events before sending
     *
     * @throws ValidationException If validation is enabled and an event fails validation
     */
    public function send(bool $validate = false): AnalyticsUrl
    {
        if (empty($this->events)) {
            // Return an analytics URL with error information about no events
            return new AnalyticsUrl('', [
                'error' => 'No events to send',
                'debug_info' => [
                    'timestamp' => new \DateTimeImmutable(),
                    'tracking_id' => $this->trackingId,
                    'client_id' => $this->clientId,
                ],
            ]);
        }

        try {
            // Validate events if requested
            if ($validate) {
                foreach ($this->events as $index => $event) {
                    try {
                        $event->validate();
                    } catch (ValidationException $e) {
                        // Rethrow with event index information
                        throw new ValidationException(sprintf('Event at index %d (%s): %s', $index, $event->getName(), $e->getMessage()), $e->getErrorCode(), sprintf('events[%d].%s', $index, $e->getField()));
                    }
                }
            }

            // Get the client ID (using explicit value or handler)
            $clientId = $this->clientId;
            if (!$clientId) {
                $clientId = $this->clientIdHandler->buildClientId();
            }
            if (!$clientId) {
                throw new ClientIdException('No client ID available. Either set it manually, provide a custom client ID handler, or ensure the default handler can retrieve a client ID.');
            }

            // Get the user ID if available
            $userId = $this->userId;
            if (!$userId && $this->userIdHandler) {
                $userId = $this->userIdHandler->buildUserId();
            }

            // Get the session ID if available
            $sessionId = $this->sessionId;
            if (!$sessionId && $this->sessionIdHandler) {
                $sessionId = $this->sessionIdHandler->buildSessionId();
            }

            // Prepare the payload
            $payload = [
                'client_id' => $clientId,
            ];

            // Add user ID if available
            if ($userId) {
                $payload['user_id'] = $userId;
            }

            // Add session ID if available (as a param on all events)
            if ($sessionId) {
                foreach ($this->events as $event) {
                    $event->addParameter('session_id', $sessionId);
                }
            }

            // Add events
            $payload['events'] = [];
            foreach ($this->events as $event) {
                $eventData = [
                    'name' => $event->getName(),
                    'params' => $event->getParameters(),
                ];

                $payload['events'][] = $eventData;
            }

            // Store parameters for debugging
            $debugPayload = [
                'payload' => $payload,
                'debug_info' => [
                    'timestamp' => new \DateTimeImmutable(),
                    'client_id' => $clientId,
                    'tracking_id' => $this->trackingId,
                    'debug_mode' => $this->debugMode,
                ],
                'raw_json' => json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'events' => [],
            ];

            // Store individual events for easier debugging
            foreach ($this->events as $index => $event) {
                $debugPayload['events'][] = [
                    'index' => $index,
                    'name' => $event->getName(),
                    'params' => $event->getParameters(),
                ];
            }

            // Send the request
            $response = $this->httpClient->sendGA4Request(
                $this->trackingId,
                $this->apiSecret,
                $payload,
                $this->debugMode
            );

            // Add response info
            $debugPayload['response'] = [
                'status_code' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'content' => $response->getContent(),
            ];

            // Generate URL and dispatch event for data collector
            $url = $this->getRequestUrl();
            $this->eventDispatcher->dispatch(new AnalyticsRequest($url, $debugPayload));

            // Store the debug payload for the last request
            $this->lastSentParameters = $debugPayload;

            // Clear events after sending
            $this->events = [];

            // Return enhanced analytics URL object with all the details
            return new AnalyticsUrl($url, $debugPayload);
        } catch (\Throwable $e) {
            $this->logger->error('Error sending GA4 request', [
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            // Create analytics URL with error information
            $errorPayload = [
                'error' => $e->getMessage(),
                'debug_info' => [
                    'exception_class' => get_class($e),
                    'timestamp' => new \DateTimeImmutable(),
                    'tracking_id' => $this->trackingId,
                    'client_id' => $this->clientId,
                ],
            ];

            return new AnalyticsUrl('', $errorPayload);
        }
    }

    /**
     * Get the request URL for GA4.
     */
    private function getRequestUrl(): string
    {
        // Add tracking of current request for internal use
        $this->getCurrentRequest();

        return sprintf(
            'https://www.google-analytics.com/%smp/collect?measurement_id=%s&api_secret=%s',
            $this->debugMode ? 'debug/' : '',
            $this->trackingId,
            $this->apiSecret
        );
    }

    /**
     * Get current request from request stack.
     * This method is used to ensure the requestStack property is read.
     */
    private function getCurrentRequest(): ?\Symfony\Component\HttpFoundation\Request
    {
        return $this->requestStack->getCurrentRequest();
    }
}
