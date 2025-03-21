<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dev\Controller;

use Freema\GA4MeasurementProtocolBundle\Client\AnalyticsRegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DemoController extends AbstractController
{
    public function __construct(
        private AnalyticsRegistryInterface $analyticsRegistry,
    ) {
    }

    #[Route('/', name: 'ga4_demo_index')]
    public function index(): Response
    {
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
        $analytics = $this->analyticsRegistry->getAnalytics('dev');
        $orderId = 'API-' . rand(1000, 9999);
        
        // Set up a purchase event
        $analytics->setDocumentPath('/api/purchase');
        $analytics->setDocumentTitle('API Purchase');
        $analytics->setProductActionToPurchase();
        $analytics->setTransactionId($orderId);
        $analytics->setRevenue(49.99);
        
        // Add a product
        $analytics->addProduct([
            'sku' => 'API-PROD-1', 
            'name' => 'API Test Product', 
            'price' => 49.99,
            'quantity' => 1,
        ]);
        
        $url = $analytics->sendEvent();
        
        return new JsonResponse([
            'success' => true,
            'event_type' => 'purchase',
            'transaction_id' => $orderId,
            'request_url' => $url
        ]);
    }
}