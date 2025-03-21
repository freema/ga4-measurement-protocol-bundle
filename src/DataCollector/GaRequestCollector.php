<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\DataCollector;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class GaRequestCollector extends DataCollector implements EventSubscriberInterface
{
    /**
     * @var GaRequest[]
     */
    private array $requestStore = [];

    public function collect(Request $request, Response $response, ?\Throwable $exception = null)
    {
        foreach ($this->requestStore as $sobRequest) {
            $this->data[] = [
                'uri' => $sobRequest->getRequestUri(),
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
    }

    public function getCount(): int
    {
        return count($this->data);
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getData(): array
    {
        return is_array($this->data) ? $this->data : [];
    }

    public function addRequest(GaRequest $request): void
    {
        $this->requestStore[] = $request;
    }

    public static function getSubscribedEvents(): array
    {
        return [GaRequest::class => 'addRequest'];
    }
}
