<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Domain;

/**
 * AnalyticsUrl represents the result of an analytics request
 * including the URL, request parameters, and response details.
 */
final class AnalyticsUrl
{
    private string $url;
    private array $parameters;
    private ?array $events;
    private ?array $response;
    private ?string $rawJson;
    private ?array $debugInfo;

    /**
     * Create a new AnalyticsUrl instance.
     *
     * @param string $url  The analytics endpoint URL
     * @param array  $data the request data including parameters, events, response, etc
     */
    public function __construct(string $url, array $data = [])
    {
        $this->url = $url;
        $this->parameters = $data['payload'] ?? $data;
        $this->events = $data['events'] ?? null;
        $this->response = $data['response'] ?? null;
        $this->rawJson = $data['raw_json'] ?? null;
        $this->debugInfo = $data['debug_info'] ?? null;
    }

    /**
     * Get the request URL.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Get all request parameters.
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get the list of events in the request.
     */
    public function getEvents(): ?array
    {
        return $this->events;
    }

    /**
     * Get the event names included in the request.
     *
     * @return string[] Array of event names
     */
    public function getEventNames(): array
    {
        if (!$this->events) {
            if (isset($this->parameters['events'])) {
                return array_map(function ($event) {
                    return $event['name'] ?? 'unknown';
                }, $this->parameters['events']);
            }

            return [];
        }

        return array_map(function ($event) {
            return $event['name'] ?? 'unknown';
        }, $this->events);
    }

    /**
     * Get the raw JSON payload.
     */
    public function getRawJson(): ?string
    {
        return $this->rawJson;
    }

    /**
     * Get the response details.
     */
    public function getResponse(): ?array
    {
        return $this->response;
    }

    /**
     * Get the response status code.
     */
    public function getStatusCode(): ?int
    {
        return $this->response['status_code'] ?? null;
    }

    /**
     * Get the response content.
     */
    public function getResponseContent(): ?string
    {
        return $this->response['content'] ?? null;
    }

    /**
     * Get debug information.
     */
    public function getDebugInfo(): ?array
    {
        return $this->debugInfo;
    }

    /**
     * Check if the request was successful.
     */
    public function isSuccessful(): bool
    {
        $statusCode = $this->getStatusCode();

        return null !== $statusCode && $statusCode >= 200 && $statusCode < 300;
    }

    /**
     * Get the number of events in the request.
     */
    public function getEventCount(): int
    {
        if ($this->events) {
            return count($this->events);
        }

        if (isset($this->parameters['events'])) {
            return count($this->parameters['events']);
        }

        return 0;
    }

    /**
     * Get the client ID used in the request.
     */
    public function getClientId(): ?string
    {
        return $this->parameters['client_id'] ?? ($this->debugInfo['client_id'] ?? null);
    }

    /**
     * Get any validation errors that might have occurred.
     */
    public function getValidationErrors(): array
    {
        if (isset($this->debugInfo['validation_errors'])) {
            return $this->debugInfo['validation_errors'];
        }

        return [];
    }

    /**
     * Convert the object to an array.
     */
    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'parameters' => $this->parameters,
            'events' => $this->events,
            'response' => $this->response,
            'debug_info' => $this->debugInfo,
            'is_successful' => $this->isSuccessful(),
        ];
    }

    /**
     * Get the request as a formatted JSON string.
     */
    public function toJson(): string
    {
        $json = json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return false === $json ? '{}' : $json;
    }

    /**
     * String representation of the analytics URL.
     */
    public function __toString(): string
    {
        return $this->url;
    }
}
