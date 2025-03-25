<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dev\Controller;

use Freema\GA4MeasurementProtocolBundle\Client\AnalyticsRegistryInterface;
use Freema\GA4MeasurementProtocolBundle\Dto\Event\PageViewEvent;
use Freema\GA4MeasurementProtocolBundle\Dto\Event\PurchaseEvent;
use Freema\GA4MeasurementProtocolBundle\Dto\Item\ItemDto;
use Freema\GA4MeasurementProtocolBundle\Factory\EventFactory;
use Freema\GA4MeasurementProtocolBundle\Factory\RequestFactory;
use Freema\GA4MeasurementProtocolBundle\GA4\Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DemoController extends AbstractController
{
    private EventFactory $eventFactory;
    private RequestFactory $requestFactory;
    
    public function __construct(
        private AnalyticsRegistryInterface $analyticsRegistry,
    ) {
        $this->eventFactory = new EventFactory();
        $this->requestFactory = new RequestFactory();
    }

    #[Route('/', name: 'ga4_demo_index')]
    public function index(): Response
    {
        // Legacy way (using AnalyticsGA4 facade)
        $analytics = $this->analyticsRegistry->getAnalytics('dev');
        
        // Send a pageview for the demo page
        $analytics->setDocumentPath('/');
        $analytics->setDocumentTitle('GA4 Demo Page');
        $url = $analytics->sendPageview();
        
        return new Response(
            '<!DOCTYPE html>
            <html>
            <head>
                <title>GA4 Measurement Protocol Demo</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; line-height: 1.6; }
                    .container { max-width: 800px; margin: 0 auto; }
                    h1 { color: #333; }
                    .card { background: #f5f5f5; border-radius: 5px; padding: 20px; margin-bottom: 20px; }
                    .code { background: #ececec; padding: 10px; border-radius: 3px; font-family: monospace; overflow-x: auto; }
                    a.button { display: inline-block; background: #4285f4; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; margin-right: 10px; }
                    a.button:hover { background: #3367d6; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>GA4 Measurement Protocol Demo</h1>
                    
                    <div class="card">
                        <h2>Page View Event Sent</h2>
                        <p>A page view event was automatically sent when this page loaded.</p>
                        <div class="code">' . htmlspecialchars($url) . '</div>
                    </div>
                    
                    <div class="card">
                        <h2>Test Events</h2>
                        <p>Click the buttons below to test different GA4 events:</p>
                        <p>
                            <a href="/purchase" class="button">Purchase Event</a>
                            <a href="/purchase-dto" class="button">Purchase Event (DTO)</a>
                            <a href="/custom-event" class="button">Custom Event</a>
                            <a href="/payload-example" class="button">JSON Payload Example</a>
                        </p>
                    </div>
                    
                    <div class="card">
                        <h2>API Tests</h2>
                        <p>API endpoints for testing programmatic access:</p>
                        <ul>
                            <li><code>/api/pageview</code> - Trigger a page view event via API</li>
                            <li><code>/api/purchase</code> - Trigger a purchase event via API</li>
                        </ul>
                    </div>
                </div>
            </body>
            </html>'
        );
    }
    
    #[Route('/purchase', name: 'ga4_demo_purchase')]
    public function purchase(): Response
    {
        // Legacy way (using AnalyticsGA4 facade)
        $analytics = $this->analyticsRegistry->getAnalytics('dev');
        $orderId = 'ORDER-' . rand(1000, 9999);
        
        // Set up a purchase event
        $analytics->setDocumentPath('/purchase');
        $analytics->setDocumentTitle('GA4 Purchase Demo');
        $analytics->setProductActionToPurchase();
        $analytics->setTransactionId($orderId);
        $analytics->setRevenue(99.99);
        $analytics->setTax(19.99);
        $analytics->setShipping(4.99);
        
        // Add a product
        $analytics->addProduct([
            'sku' => 'SKU-123', 
            'name' => 'Test Product', 
            'price' => 99.99,
            'quantity' => 1,
            'brand' => 'Test Brand'
        ]);
        
        $url = $analytics->sendEvent();
        
        return new Response(
            '<!DOCTYPE html>
            <html>
            <head>
                <title>GA4 Measurement Protocol - Purchase Event</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; line-height: 1.6; }
                    .container { max-width: 800px; margin: 0 auto; }
                    h1 { color: #333; }
                    .card { background: #f5f5f5; border-radius: 5px; padding: 20px; margin-bottom: 20px; }
                    .code { background: #ececec; padding: 10px; border-radius: 3px; font-family: monospace; overflow-x: auto; }
                    a.button { display: inline-block; background: #4285f4; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; }
                    a.button:hover { background: #3367d6; }
                    .success { color: #0f5132; background-color: #d1e7dd; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>Purchase Event</h1>
                    
                    <div class="success">
                        <strong>Success!</strong> Purchase event has been sent to GA4.
                    </div>
                    
                    <div class="card">
                        <h2>Event Details</h2>
                        <ul>
                            <li><strong>Transaction ID:</strong> ' . $orderId . '</li>
                            <li><strong>Revenue:</strong> $99.99</li>
                            <li><strong>Tax:</strong> $19.99</li>
                            <li><strong>Shipping:</strong> $4.99</li>
                            <li><strong>Product:</strong> Test Product (SKU-123)</li>
                        </ul>
                    </div>
                    
                    <div class="card">
                        <h2>Request URL</h2>
                        <div class="code">' . htmlspecialchars($url) . '</div>
                    </div>
                    
                    <p><a href="/" class="button">Back to Homepage</a></p>
                </div>
            </body>
            </html>'
        );
    }
    
    #[Route('/purchase-dto', name: 'ga4_demo_purchase_dto')]
    public function purchaseDto(): Response
    {
        // New way (using DTOs directly)
        /** @var Service $service */
        $service = $this->analyticsRegistry->getService('dev');
        $orderId = 'DTO-ORDER-' . rand(1000, 9999);
        
        // Create a purchase event
        $purchaseEvent = $this->eventFactory->createPurchaseEvent();
        $purchaseEvent->setTransactionId($orderId);
        $purchaseEvent->setValue(199.99);
        $purchaseEvent->setCurrency('USD');
        $purchaseEvent->setTax(39.99);
        $purchaseEvent->setShipping(9.99);
        $purchaseEvent->setAffiliation('Web Store');
        
        // Create and add a product
        $item = $this->eventFactory->createItem();
        $item->setId('SKU-456');
        $item->setName('Premium Product');
        $item->setBrand('Premium Brand');
        $item->setPrice(199.99);
        $item->setQuantity(1);
        $purchaseEvent->addItem($item);
        
        // Create the request
        $request = $this->requestFactory->createRequest();
        $request->setClientId('example-client-id-123');
        $request->addEvent($purchaseEvent);
        
        // Send the request
        $service->send($request);
        
        $url = $service->buildEndpointUrl();
        
        return new Response(
            '<!DOCTYPE html>
            <html>
            <head>
                <title>GA4 Measurement Protocol - Purchase Event (DTO)</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; line-height: 1.6; }
                    .container { max-width: 800px; margin: 0 auto; }
                    h1 { color: #333; }
                    .card { background: #f5f5f5; border-radius: 5px; padding: 20px; margin-bottom: 20px; }
                    .code { background: #ececec; padding: 10px; border-radius: 3px; font-family: monospace; overflow-x: auto; }
                    a.button { display: inline-block; background: #4285f4; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; }
                    a.button:hover { background: #3367d6; }
                    .success { color: #0f5132; background-color: #d1e7dd; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>Purchase Event (DTO)</h1>
                    
                    <div class="success">
                        <strong>Success!</strong> Purchase event has been sent to GA4 using the new DTO classes.
                    </div>
                    
                    <div class="card">
                        <h2>Event Details</h2>
                        <ul>
                            <li><strong>Transaction ID:</strong> ' . $orderId . '</li>
                            <li><strong>Revenue:</strong> $199.99</li>
                            <li><strong>Tax:</strong> $39.99</li>
                            <li><strong>Shipping:</strong> $9.99</li>
                            <li><strong>Product:</strong> Premium Product (SKU-456)</li>
                        </ul>
                    </div>
                    
                    <div class="card">
                        <h2>DTO Code Example</h2>
                        <div class="code">
<pre>// Create a purchase event
$purchaseEvent = $eventFactory->createPurchaseEvent();
$purchaseEvent->setTransactionId("' . $orderId . '");
$purchaseEvent->setValue(199.99);
$purchaseEvent->setCurrency("USD");
$purchaseEvent->setTax(39.99);
$purchaseEvent->setShipping(9.99);

// Create and add a product
$item = $eventFactory->createItem();
$item->setId("SKU-456");
$item->setName("Premium Product");
$item->setBrand("Premium Brand");
$item->setPrice(199.99);
$item->setQuantity(1);
$purchaseEvent->addItem($item);

// Create the request
$request = $requestFactory->createRequest();
$request->setClientId("example-client-id-123");
$request->addEvent($purchaseEvent);

// Send the request
$service->send($request);</pre>
                        </div>
                    </div>
                    
                    <div class="card">
                        <h2>Request URL</h2>
                        <div class="code">' . htmlspecialchars($url) . '</div>
                    </div>
                    
                    <p><a href="/" class="button">Back to Homepage</a></p>
                </div>
            </body>
            </html>'
        );
    }
    
    #[Route('/custom-event', name: 'ga4_demo_custom_event')]
    public function customEvent(): Response
    {
        $analytics = $this->analyticsRegistry->getAnalytics('dev');
        
        // Set up a custom event
        $analytics->setDocumentPath('/custom-event');
        $analytics->setDocumentTitle('GA4 Custom Event Demo');
        $analytics->setEventName('button_click');
        $analytics->addCustomParameter('ep.event_category', 'engagement');
        $analytics->addCustomParameter('ep.event_label', 'demo_button');
        $analytics->addCustomParameter('ep.value', '1');
        
        $url = $analytics->sendEvent();
        
        return new Response(
            '<!DOCTYPE html>
            <html>
            <head>
                <title>GA4 Measurement Protocol - Custom Event</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; line-height: 1.6; }
                    .container { max-width: 800px; margin: 0 auto; }
                    h1 { color: #333; }
                    .card { background: #f5f5f5; border-radius: 5px; padding: 20px; margin-bottom: 20px; }
                    .code { background: #ececec; padding: 10px; border-radius: 3px; font-family: monospace; overflow-x: auto; }
                    a.button { display: inline-block; background: #4285f4; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; }
                    a.button:hover { background: #3367d6; }
                    .success { color: #0f5132; background-color: #d1e7dd; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>Custom Event</h1>
                    
                    <div class="success">
                        <strong>Success!</strong> Custom event has been sent to GA4.
                    </div>
                    
                    <div class="card">
                        <h2>Event Details</h2>
                        <ul>
                            <li><strong>Event Name:</strong> button_click</li>
                            <li><strong>Event Category:</strong> engagement</li>
                            <li><strong>Event Label:</strong> demo_button</li>
                            <li><strong>Value:</strong> 1</li>
                        </ul>
                    </div>
                    
                    <div class="card">
                        <h2>Request URL</h2>
                        <div class="code">' . htmlspecialchars($url) . '</div>
                    </div>
                    
                    <p><a href="/" class="button">Back to Homepage</a></p>
                </div>
            </body>
            </html>'
        );
    }
    
    #[Route('/api/pageview', name: 'ga4_api_pageview')]
    public function apiPageview(): JsonResponse
    {
        // Legacy way
        $analytics = $this->analyticsRegistry->getAnalytics('dev');
        
        // Send a pageview
        $analytics->setDocumentPath('/api/page');
        $analytics->setDocumentTitle('API Pageview');
        $url = $analytics->sendPageview();
        
        return new JsonResponse([
            'success' => true,
            'event_type' => 'pageview',
            'request_url' => $url
        ]);
    }
    
    #[Route('/api/purchase', name: 'ga4_api_purchase')]
    public function apiPurchase(): JsonResponse
    {
        // New way with DTOs
        /** @var Service $service */
        $service = $this->analyticsRegistry->getService('dev');
        $orderId = 'API-DTO-' . rand(1000, 9999);
        
        // Create a purchase event
        $purchaseEvent = $this->eventFactory->createPurchaseEvent();
        $purchaseEvent->setTransactionId($orderId);
        $purchaseEvent->setValue(49.99);
        $purchaseEvent->setCurrency('USD');
        
        // Create and add a product
        $item = $this->eventFactory->createItem();
        $item->setId('API-PROD-2');
        $item->setName('API Test Product (DTO)');
        $item->setPrice(49.99);
        $item->setQuantity(1);
        $purchaseEvent->addItem($item);
        
        // Create the request
        $request = $this->requestFactory->createRequest();
        $request->setClientId('api-client-id-123');
        $request->addEvent($purchaseEvent);
        
        // Send the request
        $service->send($request);
        
        $url = $service->buildEndpointUrl();
        
        // Get the sent request payload for demonstration purposes
        $payload = $request->export();
        $jsonPayload = json_encode($payload, JSON_PRETTY_PRINT);
        
        return new JsonResponse([
            'success' => true,
            'event_type' => 'purchase',
            'transaction_id' => $orderId,
            'method' => 'dto',
            'request_url' => $url,
            'payload' => $payload,
            'payload_json' => $jsonPayload
        ]);
    }
    
    #[Route('/payload-example', name: 'ga4_payload_example')]
    public function payloadExample(): Response
    {
        // Create a sample request to display the JSON payload structure
        $purchaseEvent = $this->eventFactory->createPurchaseEvent();
        $purchaseEvent->setTransactionId('EXAMPLE-TX-1234');
        $purchaseEvent->setValue(199.99);
        $purchaseEvent->setCurrency('USD');
        $purchaseEvent->setTax(20.00);
        $purchaseEvent->setShipping(15.00);
        $purchaseEvent->setCoupon('SUMMER10');
        $purchaseEvent->setAffiliation('Online Store');
        
        // Add items to the purchase
        $item1 = $this->eventFactory->createItem();
        $item1->setId('SKU123');
        $item1->setName('Premium Widget');
        $item1->setPrice(149.99);
        $item1->setQuantity(1);
        $item1->setBrand('WidgetCo');
        $item1->setCategory('Electronics');
        $purchaseEvent->addItem($item1);
        
        $item2 = $this->eventFactory->createItem();
        $item2->setId('SKU456');
        $item2->setName('Widget Accessory');
        $item2->setPrice(49.99);
        $item2->setQuantity(1);
        $item2->setBrand('WidgetCo');
        $item2->setCategory('Accessories');
        $purchaseEvent->addItem($item2);
        
        // Create a page view event
        $pageViewEvent = $this->eventFactory->createPageViewEvent();
        $pageViewEvent->setPageLocation('/purchase/confirmation');
        $pageViewEvent->setPageTitle('Purchase Confirmation');
        
        // Create the request with multiple events
        $request = $this->requestFactory->createRequest();
        $request->setClientId('example-client-id-123456');
        $request->setUserId('user-12345');
        $request->setTimestampMicros((int)(microtime(true) * 1000000));
        // User language and user agent can be added as custom parameters to events instead
        $pageViewEvent->addParameter('user_language', 'en-US');
        $pageViewEvent->addParameter('user_agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)');
        $request->addEvent($purchaseEvent);
        $request->addEvent($pageViewEvent);
        
        // Convert to array and then JSON
        $payload = $request->export();
        $jsonPayload = json_encode($payload, JSON_PRETTY_PRINT);
        
        /** @var Service $service */
        $service = $this->analyticsRegistry->getService('dev');
        $endpoint = $service->buildEndpointUrl();
        
        return new Response(
            '<!DOCTYPE html>
            <html>
            <head>
                <title>GA4 Measurement Protocol - JSON Payload Example</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; line-height: 1.6; }
                    .container { max-width: 900px; margin: 0 auto; }
                    h1, h2 { color: #333; }
                    .card { background: #f5f5f5; border-radius: 5px; padding: 20px; margin-bottom: 20px; }
                    .code { background: #ececec; padding: 10px; border-radius: 3px; font-family: monospace; overflow-x: auto; white-space: pre; }
                    a.button { display: inline-block; background: #4285f4; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; }
                    a.button:hover { background: #3367d6; }
                    .info { color: #055160; background-color: #cff4fc; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>GA4 Measurement Protocol JSON Payload</h1>
                    
                    <div class="info">
                        <strong>Information:</strong> This page demonstrates the JSON payload structure sent to the GA4 Measurement Protocol.
                    </div>
                    
                    <div class="card">
                        <h2>Endpoint URL</h2>
                        <div class="code">' . htmlspecialchars($endpoint) . '</div>
                    </div>
                    
                    <div class="card">
                        <h2>JSON Payload Structure</h2>
                        <p>The following JSON is sent as the request body in a POST request:</p>
                        <div class="code">' . htmlspecialchars($jsonPayload) . '</div>
                    </div>
                    
                    <div class="card">
                        <h2>Key Components</h2>
                        <ul>
                            <li><strong>client_id:</strong> Required. Uniquely identifies a user instance of a web client.</li>
                            <li><strong>user_id:</strong> Optional. Developer-defined ID for the user.</li>
                            <li><strong>timestamp_micros:</strong> Optional. Unix timestamp in microseconds.</li>
                            <li><strong>events:</strong> Array of event objects to be logged.</li>
                            <li><strong>events.name:</strong> Required. The name of the event.</li>
                            <li><strong>events.params:</strong> Optional. Parameters associated with the event.</li>
                            <li><strong>events.items:</strong> Optional. Array of items for ecommerce events.</li>
                        </ul>
                    </div>
                    
                    <p><a href="/" class="button">Back to Homepage</a></p>
                </div>
            </body>
            </html>'
        );
    }
}