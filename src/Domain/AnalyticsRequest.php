<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Domain;

/**
 * Represents a request to the GA4 Measurement Protocol API.
 * This is a domain object that contains all information about a GA4 request.
 */
class AnalyticsRequest
{
    /**
     * @param string $requestUri The GA4 Measurement Protocol endpoint URI
     * @param array  $parameters Parameters sent with the request, including payload and status info
     */
    public function __construct(
        private string $requestUri,
        private array $parameters = [],
    ) {
    }

    /**
     * Get the request URI.
     */
    public function getRequestUri(): string
    {
        return $this->requestUri;
    }

    /**
     * Get all parameters associated with this request.
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get all events for this request.
     *
     * @return array Array of event data
     */
    public function getEvents(): array
    {
        if (isset($this->parameters['events']) && is_array($this->parameters['events'])) {
            return $this->parameters['events'];
        }

        if (isset($this->parameters['payload']['events'])) {
            $events = [];
            foreach ($this->parameters['payload']['events'] as $index => $event) {
                $events[] = [
                    'index' => $index,
                    'name' => $event['name'] ?? 'unknown',
                    'params' => $event['params'] ?? [],
                ];
            }

            return $events;
        }

        return [];
    }

    /**
     * Get the event names for this request.
     *
     * @return array Array of event names
     */
    public function getEventNames(): array
    {
        $events = $this->getEvents();
        $names = [];

        foreach ($events as $event) {
            $names[] = $event['name'] ?? 'unknown';
        }

        return $names;
    }

    /**
     * Get the first event name (for backward compatibility).
     *
     * @return string|null The event name or null if not available
     */
    public function getEventName(): ?string
    {
        $names = $this->getEventNames();

        return !empty($names) ? $names[0] : null;
    }

    /**
     * Get the raw JSON payload for this request.
     *
     * @return string|null The JSON payload or null if not available
     */
    public function getRawJson(): ?string
    {
        return $this->parameters['raw_json'] ?? null;
    }

    /**
     * Get the status code for this request.
     *
     * @return int|null The status code or null if not available
     */
    public function getStatusCode(): ?int
    {
        if (isset($this->parameters['response']['status_code'])) {
            return $this->parameters['response']['status_code'];
        }

        return $this->parameters['status_code'] ?? null;
    }

    /**
     * Get the response headers for this request.
     *
     * @return array The response headers or empty array if not available
     */
    public function getResponseHeaders(): array
    {
        return $this->parameters['response']['headers'] ?? [];
    }

    /**
     * Get the response content for this request.
     *
     * @return string|null The response content or null if not available
     */
    public function getResponseContent(): ?string
    {
        return $this->parameters['response']['content'] ?? null;
    }

    /**
     * Check if this request was successful.
     *
     * @return bool True if the request was successful, false otherwise
     */
    public function isSuccessful(): bool
    {
        $statusCode = $this->getStatusCode();

        return null !== $statusCode && $statusCode >= 200 && $statusCode < 300;
    }

    /**
     * Get timestamp when the request was made.
     *
     * @return \DateTimeInterface|null The timestamp or null if not available
     */
    public function getTimestamp(): ?\DateTimeInterface
    {
        if (isset($this->parameters['debug_info']['timestamp'])) {
            return $this->parameters['debug_info']['timestamp'];
        }

        return $this->parameters['timestamp'] ?? null;
    }

    /**
     * Get the client ID used in this request.
     */
    public function getClientId(): ?string
    {
        if (isset($this->parameters['payload']['client_id'])) {
            return $this->parameters['payload']['client_id'];
        }

        if (isset($this->parameters['debug_info']['client_id'])) {
            return $this->parameters['debug_info']['client_id'];
        }

        return null;
    }

    /**
     * Get the tracking ID (measurement ID) used in this request.
     */
    public function getTrackingId(): ?string
    {
        return $this->parameters['debug_info']['tracking_id'] ?? null;
    }

    /**
     * Convert the object to an array.
     */
    public function toArray(): array
    {
        return [
            'request_uri' => $this->requestUri,
            'parameters' => $this->parameters,
            'events' => $this->getEvents(),
            'status_code' => $this->getStatusCode(),
            'is_successful' => $this->isSuccessful(),
            'timestamp' => $this->getTimestamp(),
        ];
    }
}
