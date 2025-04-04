<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dev\Controller;

use Freema\GA4MeasurementProtocolBundle\Client\AnalyticsRegistryInterface;
use Freema\GA4MeasurementProtocolBundle\Domain\Item;
use Freema\GA4MeasurementProtocolBundle\Event\CustomEvent;
use Freema\GA4MeasurementProtocolBundle\Event\Ecommerce\PurchaseEvent;
use Freema\GA4MeasurementProtocolBundle\Event\PageViewEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DemoController extends AbstractController
{
    public function __construct(
        private readonly AnalyticsRegistryInterface $analyticsRegistry,
    ) {
    }

    #[Route('/', name: 'ga4_demo_index')]
    public function index(): Response
    {
        // Get the client for the 'dev' configuration
        $client = $this->analyticsRegistry->getClient('dev');
        
        // Create a page view event
        $pageViewEvent = new PageViewEvent();
        $pageViewEvent->setDocumentPath('/');
        $pageViewEvent->setDocumentTitle('GA4 Demo Page');
        $pageViewEvent->setDocumentReferrer('');
        
        // Add the event to the client
        $client->addEvent($pageViewEvent);
        
        try {
            // Send the event
            $analyticsUrl = $client->send();
            $error = null;
        } catch (\Exception $e) {
            $analyticsUrl = null;
            $error = $e->getMessage();
        }
        
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
                        ' . ($error ? '<div class="error" style="color: #721c24; background-color: #f8d7da; padding: 15px; border-radius: 4px; margin-bottom: 20px;"><strong>Error:</strong> ' . htmlspecialchars($error) . '</div>' : '') . '
                        <div class="code">' . ($analyticsUrl ? htmlspecialchars($analyticsUrl->getUrl()) : 'No URL available due to error') . '</div>
                    </div>
                    
                    ' . ($analyticsUrl ? '
                    <div class="card">
                        <h2>Response Details</h2>
                        <h3>Status Code</h3>
                        <div class="code">' . htmlspecialchars((string)$analyticsUrl->getStatusCode()) . '</div>
                        
                        <h3>Response Content</h3>
                        <div class="code">' . htmlspecialchars($analyticsUrl->getResponseContent() ?? 'No response content') . '</div>
                        
                        <h3>Raw JSON</h3>
                        <div class="code">' . htmlspecialchars($analyticsUrl->getRawJson() ?? 'No raw JSON available') . '</div>
                        
                        <h3>Debug Info</h3>
                        <div class="code">' . htmlspecialchars(json_encode($analyticsUrl->getDebugInfo() ?? [], JSON_PRETTY_PRINT)) . '</div>
                        
                        <h3>Event Count</h3>
                        <div>Total events sent: ' . $analyticsUrl->getEventCount() . '</div>
                        
                        <h3>Event Names</h3>
                        <div class="code">' . implode(', ', $analyticsUrl->getEventNames()) . '</div>
                    </div>' : '') . '
                    
                    <div class="card">
                        <h2>Test Events</h2>
                        <p>Click the buttons below to test different GA4 events:</p>
                        <p>
                            <a href="/purchase" class="button">Purchase Event</a>
                            <a href="/custom-event" class="button">Custom Event</a>
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
        // Get the client for the 'dev' configuration
        $client = $this->analyticsRegistry->getClient('dev');
        $orderId = 'ORDER-' . rand(1000, 9999);
        
        // Create a purchase event
        $purchaseEvent = new PurchaseEvent();
        $purchaseEvent->setTransactionId($orderId);
        $purchaseEvent->setValue(99.99);
        $purchaseEvent->setCurrency('USD');
        $purchaseEvent->setTax(19.99);
        $purchaseEvent->setShipping(4.99);
        
        // Create a product item
        $item = new Item();
        $item->setItemId('SKU-123');
        $item->setItemName('Test Product');
        $item->setPrice(99.99);
        $item->setQuantity(1);
        $item->setItemBrand('Test Brand');
        
        // Add the item to the purchase event
        $purchaseEvent->addItem($item);
        
        try {
            // Add the event to the client and send
            $client->addValidatedEvent($purchaseEvent);
            $analyticsUrl = $client->send();
            $error = null;
        } catch (\Exception $e) {
            $analyticsUrl = null;
            $error = $e->getMessage();
        }
        
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
                    
                    ' . ($error 
                        ? '<div class="error" style="color: #721c24; background-color: #f8d7da; padding: 15px; border-radius: 4px; margin-bottom: 20px;"><strong>Error:</strong> ' . htmlspecialchars($error) . '</div>'
                        : '<div class="success"><strong>Success!</strong> Purchase event has been sent to GA4.</div>'
                    ) . '
                    
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
                        <div class="code">' . ($analyticsUrl ? htmlspecialchars($analyticsUrl->getUrl()) : 'No URL available due to error') . '</div>
                    </div>
                    
                    ' . ($analyticsUrl ? '
                    <div class="card">
                        <h2>Response Details</h2>
                        <h3>Status Code</h3>
                        <div class="code">' . htmlspecialchars((string)$analyticsUrl->getStatusCode()) . '</div>
                        
                        <h3>Response Content</h3>
                        <div class="code">' . htmlspecialchars($analyticsUrl->getResponseContent() ?? 'No response content') . '</div>
                        
                        <h3>Raw JSON</h3>
                        <div class="code">' . htmlspecialchars($analyticsUrl->getRawJson() ?? 'No raw JSON available') . '</div>
                        
                        <h3>Debug Info</h3>
                        <div class="code">' . htmlspecialchars(json_encode($analyticsUrl->getDebugInfo() ?? [], JSON_PRETTY_PRINT)) . '</div>
                        
                        <h3>Event Count</h3>
                        <div>Total events sent: ' . $analyticsUrl->getEventCount() . '</div>
                        
                        <h3>Event Names</h3>
                        <div class="code">' . implode(', ', $analyticsUrl->getEventNames()) . '</div>
                    </div>' : '') . '
                    
                    <p><a href="/" class="button">Back to Homepage</a></p>
                </div>
            </body>
            </html>'
        );
    }
    
    #[Route('/custom-event', name: 'ga4_demo_custom_event')]
    public function customEvent(): Response
    {
        // Get the client for the 'dev' configuration
        $client = $this->analyticsRegistry->getClient('dev');
        
        // Create a custom event
        $customEvent = new CustomEvent('button_click');
        $customEvent->setDocumentPath('/custom-event');
        $customEvent->setDocumentTitle('GA4 Custom Event Demo');
        $customEvent->addParameter('event_category', 'engagement');
        $customEvent->addParameter('event_label', 'demo_button');
        $customEvent->addParameter('value', 1);
        
        try {
            // Add the event to the client and send
            $client->addEvent($customEvent);
            $analyticsUrl = $client->send();
            $error = null;
        } catch (\Exception $e) {
            $analyticsUrl = null;
            $error = $e->getMessage();
        }
        
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
                    
                    ' . ($error 
                        ? '<div class="error" style="color: #721c24; background-color: #f8d7da; padding: 15px; border-radius: 4px; margin-bottom: 20px;"><strong>Error:</strong> ' . htmlspecialchars($error) . '</div>'
                        : '<div class="success"><strong>Success!</strong> Custom event has been sent to GA4.</div>'
                    ) . '
                    
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
                        <div class="code">' . ($analyticsUrl ? htmlspecialchars($analyticsUrl->getUrl()) : 'No URL available due to error') . '</div>
                    </div>
                    
                    ' . ($analyticsUrl ? '
                    <div class="card">
                        <h2>Response Details</h2>
                        <h3>Status Code</h3>
                        <div class="code">' . htmlspecialchars((string)$analyticsUrl->getStatusCode()) . '</div>
                        
                        <h3>Response Content</h3>
                        <div class="code">' . htmlspecialchars($analyticsUrl->getResponseContent() ?? 'No response content') . '</div>
                        
                        <h3>Raw JSON</h3>
                        <div class="code">' . htmlspecialchars($analyticsUrl->getRawJson() ?? 'No raw JSON available') . '</div>
                        
                        <h3>Debug Info</h3>
                        <div class="code">' . htmlspecialchars(json_encode($analyticsUrl->getDebugInfo() ?? [], JSON_PRETTY_PRINT)) . '</div>
                        
                        <h3>Event Count</h3>
                        <div>Total events sent: ' . $analyticsUrl->getEventCount() . '</div>
                        
                        <h3>Event Names</h3>
                        <div class="code">' . implode(', ', $analyticsUrl->getEventNames()) . '</div>
                    </div>' : '') . '
                    
                    <p><a href="/" class="button">Back to Homepage</a></p>
                </div>
            </body>
            </html>'
        );
    }
    
    #[Route('/api/pageview', name: 'ga4_api_pageview')]
    public function apiPageview(): JsonResponse
    {
        // Get the client for the 'dev' configuration
        $client = $this->analyticsRegistry->getClient('dev');
        
        // Create a page view event
        $pageViewEvent = new PageViewEvent();
        $pageViewEvent->setDocumentPath('/api/page');
        $pageViewEvent->setDocumentTitle('API Pageview');
        
        try {
            // Add the event to the client and send
            $client->addEvent($pageViewEvent);
            $analyticsUrl = $client->send();
            
            return new JsonResponse([
                'success' => true,
                'event_type' => 'pageview',
                'request_url' => $analyticsUrl->getUrl()
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'event_type' => 'pageview',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    #[Route('/api/purchase', name: 'ga4_api_purchase')]
    public function apiPurchase(): JsonResponse
    {
        // Get the client for the 'dev' configuration
        $client = $this->analyticsRegistry->getClient('dev');
        $orderId = 'API-' . rand(1000, 9999);
        
        // Create a purchase event
        $purchaseEvent = new PurchaseEvent();
        $purchaseEvent->setTransactionId($orderId);
        $purchaseEvent->setValue(49.99);
        $purchaseEvent->setCurrency('USD');
        
        // Create a product item
        $item = new Item();
        $item->setItemId('API-PROD-1');
        $item->setItemName('API Test Product');
        $item->setPrice(49.99);
        $item->setQuantity(1);
        
        // Add the item to the purchase event
        $purchaseEvent->addItem($item);
        
        try {
            // Add the event to the client and send
            $client->addValidatedEvent($purchaseEvent);
            $analyticsUrl = $client->send();
            
            return new JsonResponse([
                'success' => true,
                'event_type' => 'purchase',
                'transaction_id' => $orderId,
                'request_url' => $analyticsUrl->getUrl()
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'event_type' => 'purchase',
                'transaction_id' => $orderId,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}