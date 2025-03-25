<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\DataCollector;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects GA4 Measurement Protocol requests for the Symfony Web Debug Toolbar.
 */
class GaRequestCollector extends DataCollector implements EventSubscriberInterface
{
    /**
     * @var GaRequest[]
     */
    private array $requestStore = [];

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, ?\Throwable $exception = null)
    {
        $this->data = [];
        
        foreach ($this->requestStore as $gaRequest) {
            $this->data[] = [
                'uri' => $gaRequest->getRequestUri(),
                'payload' => $gaRequest->getPayload(),
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'ga';
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->data = [];
    }

    /**
     * Get the number of collected requests.
     */
    public function getCount(): int
    {
        return count($this->data);
    }

    /**
     * Get all collected request data.
     * 
     * @return array<int, array<string, mixed>>
     */
    public function getData(): array
    {
        return is_array($this->data) ? $this->data : [];
    }

    /**
     * Add a GA request to the collection.
     */
    public function addRequest(GaRequest $request): void
    {
        $this->requestStore[] = $request;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [GaRequest::class => 'addRequest'];
    }
}