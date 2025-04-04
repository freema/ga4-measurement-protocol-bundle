<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\DataCollector;

use Freema\GA4MeasurementProtocolBundle\Domain\AnalyticsRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class GaRequestCollector extends DataCollector implements EventSubscriberInterface
{
    /**
     * @var AnalyticsRequest[]
     */
    private array $requestStore = [];

    public function collect(Request $request, Response $response, ?\Throwable $exception = null)
    {
        $this->data = [];

        foreach ($this->requestStore as $gaRequest) {
            // Add timestamp to parameters if not set
            $parameters = $gaRequest->getParameters();
            if (!isset($parameters['timestamp'])) {
                $parameters['timestamp'] = new \DateTimeImmutable();
            }

            $this->data[] = [
                'uri' => $gaRequest->getRequestUri(),
                'parameters' => $parameters,
            ];
        }
    }

    public function getName(): string
    {
        return 'ga';
    }

    public function reset()
    {
        $this->data = [];
        $this->requestStore = [];
    }

    /**
     * Get the number of GA4 requests.
     */
    public function getCount(): int
    {
        return count($this->data);
    }

    /**
     * Get all collected GA4 request data.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getData(): array
    {
        return is_array($this->data) ? $this->data : [];
    }

    /**
     * Add a GA4 request to the collector.
     */
    public function addRequest(AnalyticsRequest $request): void
    {
        $this->requestStore[] = $request;
    }

    /**
     * Get request by index.
     */
    public function getRequest(int $index): ?array
    {
        return $this->data[$index] ?? null;
    }

    /**
     * Get event types summary.
     *
     * @return array<string, int> Map of event type to count
     */
    public function getEventTypes(): array
    {
        $eventTypes = [];

        foreach ($this->data as $requestData) {
            $eventNames = [];

            // Try to extract event names from various places
            if (isset($requestData['parameters']['events']) && is_array($requestData['parameters']['events'])) {
                foreach ($requestData['parameters']['events'] as $event) {
                    if (isset($event['name'])) {
                        $eventNames[] = $event['name'];
                    }
                }
            } elseif (isset($requestData['parameters']['payload']['events'])) {
                foreach ($requestData['parameters']['payload']['events'] as $event) {
                    if (isset($event['name'])) {
                        $eventNames[] = $event['name'];
                    }
                }
            } elseif (isset($requestData['parameters']['event_name'])) {
                $eventNames[] = $requestData['parameters']['event_name'];
            }

            foreach ($eventNames as $eventName) {
                if (!isset($eventTypes[$eventName])) {
                    $eventTypes[$eventName] = 0;
                }
                ++$eventTypes[$eventName];
            }
        }

        return $eventTypes;
    }

    /**
     * Get total number of events (may be more than number of requests if batching is used).
     */
    public function getTotalEvents(): int
    {
        $total = 0;

        foreach ($this->data as $requestData) {
            if (isset($requestData['parameters']['events']) && is_array($requestData['parameters']['events'])) {
                $total += count($requestData['parameters']['events']);
            } elseif (isset($requestData['parameters']['payload']['events'])) {
                $total += count($requestData['parameters']['payload']['events']);
            } else {
                // At least one event per request
                ++$total;
            }
        }

        return $total;
    }

    public static function getSubscribedEvents(): array
    {
        return [AnalyticsRequest::class => 'addRequest'];
    }
}
