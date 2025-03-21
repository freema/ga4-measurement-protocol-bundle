# Google Analytics 4 Measurement Protocol Bundle

This bundle provides integration with Google Analytics 4 Measurement Protocol for Symfony applications. It supports tracking page views and purchase events in GA4.

## Installation

```bash
composer require freema/ga4-measurement-protocol-bundle
```

## Configuration

Add the bundle to your `config/bundles.php`:

```php
return [
    // ...
    Freema\GA4MeasurementProtocolBundle\GA4MeasurementProtocolBundle::class => ['all' => true],
    // ...
];
```

Create a configuration file `config/packages/ga4_measurement_protocol.yaml`:

```yaml
# config/packages/ga4_measurement_protocol.yaml
ga4_measurement_protocol:
  clients:
    front:
      tracking_id: 'G-XXXXXXXXXX'  # Your GA4 Measurement ID
```

## Full Configuration Reference

```yaml
ga4_measurement_protocol:
    # Optional: Specify a custom GA4 endpoint URL (default: https://region1.analytics.google.com/g/collect)
    ga4_endpoint: 'https://region1.analytics.google.com/g/collect'
    
    # Optional: Specify a custom HTTP client factory implementation
    http_client_factory: 'App\Service\MyCustomHttpClientFactory'
    
    # HTTP client configuration
    http_client:
        config:
            # Example proxy configuration
            proxy:
                http: 'proxy.example.com:3128'
                https: 'proxy.example.com:3128'
                no: ['localhost', '.example.com']
            # Other HTTP client options
            timeout: 5.0
            max_redirects: 5
    
    # Required: Define your analytics clients
    clients:
        front:
            # Required: Your GA4 Measurement ID
            tracking_id: 'G-XXXXXXXXXX'
            
            # Optional: Client-specific GA4 endpoint (overrides global setting)
            ga4_endpoint: 'https://region2.analytics.google.com/g/collect'
            
            # Optional: Fixed client ID to use for all requests
            client_id: '555.123456789'
            
            # Optional: Custom handlers
            custom_client_id_handler: 'App\Handler\MyClientIdHandler'
            custom_user_id_handler: 'App\Handler\MyUserIdHandler'
            custom_session_id_handler: 'App\Handler\MySessionIdHandler'
```

## Usage

### Sending Page Views

```php
use Freema\GA4MeasurementProtocolBundle\Client\AnalyticsRegistryInterface;

class YourController
{
    public function someAction(AnalyticsRegistryInterface $analyticsRegistry)
    {
        $analytics = $analyticsRegistry->getAnalytics('front');
        $analytics->setDocumentPath('/your-page-path');
        $analytics->setDocumentTitle('Your Page Title');
        $analytics->sendPageview();
        
        // Rest of your controller code
    }
}
```

### Sending Purchase Events

```php
use Freema\GA4MeasurementProtocolBundle\Client\AnalyticsRegistryInterface;

class YourController
{
    public function purchaseAction(AnalyticsRegistryInterface $analyticsRegistry, $order)
    {
        $analytics = $analyticsRegistry->getAnalytics('front');
        
        // Required purchase parameters
        $analytics->setProductActionToPurchase();
        $analytics->setTransactionId($order->getOrderNumber());
        $analytics->setRevenue($order->getTotal());
        
        // Optional parameters
        $analytics->setTax($order->getTax());
        $analytics->setShipping($order->getShippingCost());
        $analytics->setDiscount($order->getDiscountAmount());
        
        // Add products
        foreach ($order->getItems() as $item) {
            $analytics->addProduct([
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'price' => $item->getPrice(),
                'quantity' => $item->getQuantity(),
                'brand' => $item->getBrand(),
            ]);
        }
        
        $analytics->sendEvent();
        
        // Rest of your controller code
    }
}
```

## Development

For local development and testing, you can use the included development environment:

```bash
# Start the Docker container
task up

# Run the development server
task dev:serve

# Run tests for different Symfony versions
task test:symfony54
task test:symfony64
task test:symfony71
```

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

This bundle is available under the MIT License. See the [LICENSE](LICENSE) file for more information.