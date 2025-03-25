<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\GA4;

use Freema\GA4MeasurementProtocolBundle\DataCollector\GaRequest;
use Freema\GA4MeasurementProtocolBundle\Dto\Event\EventInterface;
use Freema\GA4MeasurementProtocolBundle\Dto\Event\PageViewEvent;
use Freema\GA4MeasurementProtocolBundle\Dto\Event\PurchaseEvent;
use Freema\GA4MeasurementProtocolBundle\Dto\Item\ItemDto;
use Freema\GA4MeasurementProtocolBundle\Dto\Request\RequestDto;
use Freema\GA4MeasurementProtocolBundle\Factory\EventFactory;
use Freema\GA4MeasurementProtocolBundle\Factory\RequestFactory;
use Freema\GA4MeasurementProtocolBundle\Http\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Facade for working with GA4 Measurement Protocol.
 * Provides backward compatibility with the original API.
 */
class AnalyticsGA4
{
    private Service $service;
    private RequestDto $request;
    private EventInterface $currentEvent;
    private LoggerInterface $logger;
    private readonly EventFactory $eventFactory;
    private readonly RequestFactory $requestFactory;
    
    /**
     * AnalyticsGA4 constructor.
     */
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EventDispatcherInterface $eventDispatcher,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->eventFactory = new EventFactory();
        $this->requestFactory = new RequestFactory();
        
        // Initialize with a temporary service - will be configured later
        $this->service = new Service($httpClient);
        
        // Initialize request and event
        $this->request = $this->requestFactory->createRequest();
        $this->currentEvent = $this->eventFactory->createCustomEvent('custom_event');
        
        // Make sure client ID is set on the request
        if (method_exists($httpClient, 'getClientId')) {
            $clientId = $httpClient->getClientId();
            if ($clientId) {
                $this->request->setClientId($clientId);
            }
        }
    }
    
    /**
     * Set the API secret.
     */
    public function setApiSecret(string $apiSecret): self
    {
        $this->service->setApiSecret($apiSecret);
        return $this;
    }
    
    /**
     * Set the protocol version (not used in GA4, kept for compatibility).
     */
    public function setProtocolVersion(string $version): self
    {
        // Not applicable for GA4 Measurement Protocol
        return $this;
    }
    
    /**
     * Set the tracking/measurement ID.
     */
    public function setTrackingId(string $trackingId): self
    {
        $this->service->setMeasurementId($trackingId);
        return $this;
    }
    
    /**
     * Set the client ID.
     */
    public function setClientId(string $clientId): self
    {
        $this->request->setClientId($clientId);
        return $this;
    }
    
    /**
     * Set the user ID.
     */
    public function setUserId(string $userId): self
    {
        $this->request->setUserId($userId);
        return $this;
    }
    
    /**
     * Set the user agent (not directly supported in GA4, kept for compatibility).
     */
    public function setUserAgentOverride(string $userAgent): self
    {
        // Not directly supported in GA4
        return $this;
    }
    
    /**
     * Set the document path.
     */
    public function setDocumentPath(string $path): self
    {
        if ($this->currentEvent instanceof PageViewEvent) {
            $this->currentEvent->setPageLocation($path);
        } else {
            $this->currentEvent->addParameter('page_location', $path);
        }
        
        return $this;
    }
    
    /**
     * Set the document referrer.
     */
    public function setDocumentReferrer(string $referrer): self
    {
        if ($this->currentEvent instanceof PageViewEvent) {
            $this->currentEvent->setPageReferrer($referrer);
        } else {
            $this->currentEvent->addParameter('page_referrer', $referrer);
        }
        
        return $this;
    }
    
    /**
     * Set the document title.
     */
    public function setDocumentTitle(string $title): self
    {
        if ($this->currentEvent instanceof PageViewEvent) {
            $this->currentEvent->setPageTitle($title);
        } else {
            $this->currentEvent->addParameter('page_title', $title);
        }
        
        return $this;
    }
    
    /**
     * Set the session ID.
     */
    public function setSessionId(string $sessionId): self
    {
        $this->currentEvent->addParameter('session_id', $sessionId);
        return $this;
    }
    
    /**
     * Set the currency.
     */
    public function setCurrency(string $currency): self
    {
        if ($this->currentEvent instanceof PurchaseEvent) {
            $this->currentEvent->setCurrency($currency);
        } else {
            $this->currentEvent->addParameter('currency', $currency);
        }
        
        return $this;
    }
    
    /**
     * Set the transaction ID.
     */
    public function setTransactionId(string $transactionId): self
    {
        if ($this->currentEvent instanceof PurchaseEvent) {
            $this->currentEvent->setTransactionId($transactionId);
        } else {
            $this->currentEvent->addParameter('transaction_id', $transactionId);
        }
        
        return $this;
    }
    
    /**
     * Set the revenue.
     */
    public function setRevenue(float $revenue): self
    {
        if ($this->currentEvent instanceof PurchaseEvent) {
            $this->currentEvent->setValue($revenue);
        } else {
            $this->currentEvent->addParameter('value', $revenue);
        }
        
        return $this;
    }
    
    /**
     * Set the tax.
     */
    public function setTax(float $tax): self
    {
        if ($this->currentEvent instanceof PurchaseEvent) {
            $this->currentEvent->setTax($tax);
        } else {
            $this->currentEvent->addParameter('tax', $tax);
        }
        
        return $this;
    }
    
    /**
     * Set the shipping.
     */
    public function setShipping(float $shipping): self
    {
        if ($this->currentEvent instanceof PurchaseEvent) {
            $this->currentEvent->setShipping($shipping);
        } else {
            $this->currentEvent->addParameter('shipping', $shipping);
        }
        
        return $this;
    }
    
    /**
     * Set the discount.
     */
    public function setDiscount(float $discount): self
    {
        $this->currentEvent->addParameter('discount', $discount);
        return $this;
    }
    
    /**
     * Set the affiliation.
     */
    public function setAffiliation(string $affiliation): self
    {
        if ($this->currentEvent instanceof PurchaseEvent) {
            $this->currentEvent->setAffiliation($affiliation);
        } else {
            $this->currentEvent->addParameter('affiliation', $affiliation);
        }
        
        return $this;
    }
    
    /**
     * Set the payment type.
     */
    public function setPaymentType(string $paymentType): self
    {
        $this->currentEvent->addParameter('payment_type', $paymentType);
        return $this;
    }
    
    /**
     * Set the shipping tier.
     */
    public function setShippingTier(string $shippingTier): self
    {
        $this->currentEvent->addParameter('shipping_tier', $shippingTier);
        return $this;
    }
    
    /**
     * Add a product to the current event.
     */
    public function addProduct(array $product): self
    {
        $item = $this->eventFactory->createItemFromArray($product);
        
        if ($this->currentEvent instanceof PurchaseEvent) {
            $this->currentEvent->addItem($item);
        } else {
            // For other event types, add items as parameters
            $items = $this->currentEvent->getParameters()['items'] ?? [];
            $items[] = $item->export();
            $this->currentEvent->addParameter('items', $items);
        }
        
        return $this;
    }
    
    /**
     * Set the event action to purchase.
     */
    public function setProductActionToPurchase(): self
    {
        // Create a new purchase event
        $purchaseEvent = $this->eventFactory->createPurchaseEvent();
        
        // Copy all parameters from the current event
        foreach ($this->currentEvent->getParameters() as $key => $value) {
            if ($key === 'items' && is_array($value)) {
                // Handle items specially
                foreach ($value as $itemData) {
                    $item = $this->eventFactory->createItemFromArray($itemData);
                    $purchaseEvent->addItem($item);
                }
            } else {
                $purchaseEvent->addParameter($key, $value);
            }
        }
        
        // Set the new event as current
        $this->currentEvent = $purchaseEvent;
        
        return $this;
    }
    
    /**
     * Set the event category.
     */
    public function setEventCategory(string $category): self
    {
        $this->currentEvent->addParameter('event_category', $category);
        return $this;
    }
    
    /**
     * Set the event action.
     */
    public function setEventAction(string $action): self
    {
        $this->currentEvent->addParameter('event_action', $action);
        return $this;
    }
    
    /**
     * Set the event name.
     */
    public function setEventName(string $name): self
    {
        // Create a new event with the specified name
        $newEvent = $this->eventFactory->createCustomEvent($name);
        
        // Copy all parameters from the current event
        foreach ($this->currentEvent->getParameters() as $key => $value) {
            $newEvent->addParameter($key, $value);
        }
        
        // Set the new event as current
        $this->currentEvent = $newEvent;
        
        return $this;
    }
    
    /**
     * Add a custom parameter to the current event.
     */
    public function addCustomParameter(string $key, string $value): self
    {
        $this->currentEvent->addParameter($key, $value);
        return $this;
    }
    
    /**
     * Set a custom endpoint.
     */
    public function setCustomEndpoint(string $endpoint): self
    {
        // Endpoint is fully managed by the Service class
        return $this;
    }
    
    /**
     * Send a page view event.
     */
    public function sendPageview(): string
    {
        // Switch to a page view event
        $pageViewEvent = $this->eventFactory->createPageViewEvent();
        
        // Copy relevant parameters
        if (isset($this->currentEvent->getParameters()['page_title'])) {
            $pageViewEvent->setPageTitle($this->currentEvent->getParameters()['page_title']);
        }
        
        if (isset($this->currentEvent->getParameters()['page_location'])) {
            $pageViewEvent->setPageLocation($this->currentEvent->getParameters()['page_location']);
        }
        
        if (isset($this->currentEvent->getParameters()['page_referrer'])) {
            $pageViewEvent->setPageReferrer($this->currentEvent->getParameters()['page_referrer']);
        }
        
        // Set the current event to the page view event
        $this->currentEvent = $pageViewEvent;
        
        // Send the event
        return $this->send();
    }
    
    /**
     * Send the current event.
     */
    public function sendEvent(): string
    {
        return $this->send();
    }
    
    /**
     * Send the event to GA4.
     */
    private function send(): string
    {
        try {
            // Add the current event to the request
            $this->request->addEvent($this->currentEvent);
            
            // Get endpoint URL for logging
            $endpoint = $this->service->buildEndpointUrl(false);
            
            // Dispatch event for data collector with payload
            $this->eventDispatcher->dispatch(new GaRequest($endpoint, $this->request->export()));
            
            // Log the request
            $this->logger->debug('Sending GA4 request', [
                'url' => $endpoint,
                'payload' => $this->request->export(),
            ]);
            
            // Send the request
            $this->service->send($this->request);
            
            // Reset for next request
            $this->request = $this->requestFactory->createRequest();
            $this->currentEvent = $this->eventFactory->createCustomEvent('custom_event');
            
            return $endpoint;
        } catch (\Throwable $e) {
            // Log the error
            $this->logger->error('Error sending GA4 request', [
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);
            
            // Return the endpoint URL that would have been called
            return $this->service->buildEndpointUrl(false);
        }
    }
}