<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Factory;

use Freema\GA4MeasurementProtocolBundle\Dto\Event\EventInterface;
use Freema\GA4MeasurementProtocolBundle\Dto\Request\RequestDto;

/**
 * Factory for creating GA4 requests.
 */
class RequestFactory
{
    /**
     * Create an empty request.
     */
    public function createRequest(): RequestDto
    {
        return new RequestDto();
    }
    
    /**
     * Create a request with a client ID.
     */
    public function createRequestWithClientId(string $clientId): RequestDto
    {
        return $this->createRequest()->setClientId($clientId);
    }
    
    /**
     * Create a request with an event.
     */
    public function createRequestWithEvent(EventInterface $event): RequestDto
    {
        return $this->createRequest()->addEvent($event);
    }
    
    /**
     * Create a request with a client ID and an event.
     */
    public function createRequestWithClientIdAndEvent(string $clientId, EventInterface $event): RequestDto
    {
        return $this->createRequest()
            ->setClientId($clientId)
            ->addEvent($event);
    }
}