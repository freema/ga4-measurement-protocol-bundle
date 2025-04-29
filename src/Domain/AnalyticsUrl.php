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
        $this->events = isset($data['events']) && is_array($data['events']) ? $data['events'] : null;
        $this->response = isset($data['response']) && is_array($data['response']) ? $data['response'] : null;
        $this->rawJson = isset($data['raw_json']) && is_string($data['raw_json']) ? $data['raw_json'] : null;

        // Handle debugInfo with proper type checking for PHPStan
        $this->debugInfo = $this->processDebugInfo($data['debug_info'] ?? null);
    }

    /**
     * Process debug_info data with proper type handling.
     *
     * @param mixed $debugInfo The debug info data to process
     *
     * @return array|null Properly typed debug info data
     */
    private function processDebugInfo($debugInfo): ?array
    {
        if (null === $debugInfo) {
            return null;
        }

        if (is_array($debugInfo)) {
            return $debugInfo;
        }

        if (is_string($debugInfo)) {
            $decoded = json_decode($debugInfo, true);
            if (JSON_ERROR_NONE === json_last_error() && is_array($decoded)) {
                return $decoded;
            }

            return ['message' => $debugInfo];
        }

        // For any other type, create an array representation
        return [
            'value' => $debugInfo,
            'type' => gettype($debugInfo),
        ];
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
            if (isset($this->parameters['events']) && is_array($this->parameters['events'])) {
                return array_map(function ($event) {
                    return is_array($event) && isset($event['name']) ? (string) $event['name'] : 'unknown';
                }, $this->parameters['events']);
            }

            return [];
        }

        return array_map(function ($event) {
            return is_array($event) && isset($event['name']) ? (string) $event['name'] : 'unknown';
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
        if (null === $this->response || !isset($this->response['status_code'])) {
            return null;
        }

        $statusCode = $this->response['status_code'];

        return is_int($statusCode) ? $statusCode : (int) $statusCode;
    }

    /**
     * Get the response content.
     */
    public function getResponseContent(): ?string
    {
        if (null === $this->response || !isset($this->response['content'])) {
            return null;
        }

        $content = $this->response['content'];

        return is_string($content) ? $content : (string) $content;
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

        if (isset($this->parameters['events']) && is_array($this->parameters['events'])) {
            return count($this->parameters['events']);
        }

        return 0;
    }

    /**
     * Get the client ID used in the request.
     */
    public function getClientId(): ?string
    {
        if (isset($this->parameters['client_id'])) {
            $clientId = $this->parameters['client_id'];

            return is_string($clientId) ? $clientId : (string) $clientId;
        }

        if (null !== $this->debugInfo && isset($this->debugInfo['client_id'])) {
            // Ensure that the returned value is a string
            $clientId = $this->debugInfo['client_id'];

            return is_string($clientId) ? $clientId : (string) $clientId;
        }

        return null;
    }

    /**
     * Get any validation errors that might have occurred.
     */
    public function getValidationErrors(): array
    {
        if (null === $this->debugInfo || !isset($this->debugInfo['validation_errors'])) {
            return [];
        }

        $errors = $this->debugInfo['validation_errors'];

        if (is_array($errors)) {
            return $errors;
        }

        return [$errors];
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
