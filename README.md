# Google Analytics 4 Measurement Protocol Bundle

This bundle provides a clean, object-oriented integration with Google Analytics 4 Measurement Protocol for Symfony applications. It uses an event-based approach to track page views, purchases, and custom events in GA4.

## Requirements

- PHP 8.1 or higher
- Symfony 5.4 or 6.4

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
      api_secret: 'YOUR_SECRET'    # Your GA4 API Secret
```

## Full Configuration Reference

```yaml
ga4_measurement_protocol:
    # HTTP client configuration
    http_client:
        config:
            # Proxy configuration (Symfony HTTP client format)
            proxy: 'http://proxy.example.com:3128'
            no_proxy: ['localhost', '.example.com']
            # Other HTTP client options
            timeout: 5.0
            max_redirects: 5
    
    # Required: Define your analytics clients
    clients:
        front:
            # Required: Your GA4 Measurement ID
            tracking_id: 'G-XXXXXXXXXX'
            
            # Required: Your GA4 API Secret
            api_secret: 'YOUR_SECRET'
            
            # Optional: Fixed client ID to use for all requests
            client_id: '555.123456789'
            
            # Optional: Enable debug mode
            debug_mode: true
            
            # Optional: Custom handlers
            custom_client_id_handler: 'App\Handler\MyClientIdHandler'
            custom_user_id_handler: 'App\Handler\MyUserIdHandler'
            custom_session_id_handler: 'App\Handler\MySessionIdHandler'
```

## Using the Bundle

The bundle uses an event-based approach where you create specific event objects, add them to the client, and then send them. This makes the code cleaner and easier to understand.

### Basic Usage

```php
use Freema\GA4MeasurementProtocolBundle\Client\AnalyticsRegistryInterface;
use Freema\GA4MeasurementProtocolBundle\Event\PageViewEvent;

// Get the analytics client
$analytics = $analyticsRegistry->getClient('front');

// Create an event
$pageViewEvent = new PageViewEvent();
$pageViewEvent->setDocumentPath('/your-page-path');
$pageViewEvent->setDocumentTitle('Your Page Title');

// Add the event and send
$analytics->addEvent($pageViewEvent);
$result = $analytics->send();
```

### Page View Example

```php
use Freema\GA4MeasurementProtocolBundle\Client\AnalyticsRegistryInterface;
use Freema\GA4MeasurementProtocolBundle\Event\PageViewEvent;

class YourController
{
    public function pageViewAction(AnalyticsRegistryInterface $analyticsRegistry)
    {
        // Get the analytics client
        $analytics = $analyticsRegistry->getClient('front');
        
        // Create a page view event
        $pageViewEvent = new PageViewEvent();
        $pageViewEvent->setDocumentPath('/your-page-path');
        $pageViewEvent->setDocumentTitle('Your Page Title');
        
        // Add the event and send
        $analytics->addEvent($pageViewEvent);
        $result = $analytics->send();
        
        // Result contains URL and parameters sent
    }
}
```

### Purchase Event Example

```php
use Freema\GA4MeasurementProtocolBundle\Client\AnalyticsRegistryInterface;
use Freema\GA4MeasurementProtocolBundle\Event\PurchaseEvent;
use Freema\GA4MeasurementProtocolBundle\Event\Item;

class YourController
{
    public function purchaseAction(AnalyticsRegistryInterface $analyticsRegistry, $order)
    {
        // Get the analytics client
        $analytics = $analyticsRegistry->getClient('front');
        
        // Create a purchase event
        $purchaseEvent = new PurchaseEvent();
        $purchaseEvent->setTransactionId($order->getOrderNumber());
        $purchaseEvent->setValue($order->getTotal());
        $purchaseEvent->setTax($order->getTax());
        $purchaseEvent->setShipping($order->getShippingCost());
        
        // Add products as items
        foreach ($order->getItems() as $orderItem) {
            $item = new Item();
            $item->setItemId($orderItem->getSku());
            $item->setItemName($orderItem->getName());
            $item->setPrice($orderItem->getPrice());
            $item->setQuantity($orderItem->getQuantity());
            $item->setItemBrand($orderItem->getBrand());
            
            $purchaseEvent->addItem($item);
        }
        
        // Add the event and send
        $analytics->addEvent($purchaseEvent);
        $result = $analytics->send();
    }
}
```

## Event Types

The bundle provides a variety of pre-defined event types that match GA4's standard events.

### E-commerce Events

```php
use Freema\GA4MeasurementProtocolBundle\Event\Ecommerce\ViewItemEvent;
use Freema\GA4MeasurementProtocolBundle\Event\Ecommerce\AddToCartEvent;
use Freema\GA4MeasurementProtocolBundle\Event\Ecommerce\BeginCheckoutEvent;
use Freema\GA4MeasurementProtocolBundle\Event\PurchaseEvent;
use Freema\GA4MeasurementProtocolBundle\Event\Item;

// Create item
$item = new Item();
$item->setItemId('SKU123');
$item->setItemName('Product Name');
$item->setPrice(29.99);
$item->setQuantity(1);
$item->setItemBrand('Brand Name');
$item->setItemCategory('Category');

// View item
$viewItemEvent = new ViewItemEvent();
$viewItemEvent->setValue(29.99);
$viewItemEvent->addItem($item);
$analytics->addEvent($viewItemEvent);

// Add to cart
$addToCartEvent = new AddToCartEvent();
$addToCartEvent->setValue(29.99);
$addToCartEvent->addItem($item);
$analytics->addEvent($addToCartEvent);

// Begin checkout
$beginCheckoutEvent = new BeginCheckoutEvent();
$beginCheckoutEvent->setValue(29.99);
$beginCheckoutEvent->addItem($item);
$analytics->addEvent($beginCheckoutEvent);

// Purchase
$purchaseEvent = new PurchaseEvent();
$purchaseEvent->setTransactionId('ORDER123');
$purchaseEvent->setValue(29.99);
$purchaseEvent->setTax(6.00);
$purchaseEvent->addItem($item);
$analytics->addEvent($purchaseEvent);

// Send all events at once
$analytics->send();
```

### Engagement Events

```php
use Freema\GA4MeasurementProtocolBundle\Event\Engagement\LoginEvent;
use Freema\GA4MeasurementProtocolBundle\Event\Engagement\SignUpEvent;

// Login
$loginEvent = new LoginEvent();
$loginEvent->setMethod('email');
$analytics->addEvent($loginEvent);

// Sign up
$signUpEvent = new SignUpEvent();
$signUpEvent->setMethod('email');
$analytics->addEvent($signUpEvent);
```

### Custom Event

```php
use Freema\GA4MeasurementProtocolBundle\Event\CustomEvent;

// Create a custom event
$customEvent = new CustomEvent('your_custom_event_name');
$customEvent->addParameter('event_category', 'user_engagement');
$customEvent->addParameter('event_action', 'click');
$customEvent->addParameter('custom_dimension1', 'value1');

// Add the event and send
$analytics->addEvent($customEvent);
$result = $analytics->send();
```

## Creating Your Own Event Classes

You can easily create your own event classes for specific events:

```php
namespace App\Analytics\Event;

use Freema\GA4MeasurementProtocolBundle\Event\AbstractEvent;

class SpecialOfferViewEvent extends AbstractEvent
{
    public function getName(): string
    {
        return 'view_special_offer';
    }
    
    public function setOfferId(string $offerId): self
    {
        $this->parameters['offer_id'] = $offerId;
        return $this;
    }
    
    public function setOfferName(string $offerName): self
    {
        $this->parameters['offer_name'] = $offerName;
        return $this;
    }
}
```

## ID Management

### Client ID

Client ID is required by GA4 to identify unique users. The bundle will:

1. Try to get it from the `_ga` cookie
2. Use a fixed value if configured:
   ```yaml
   client_id: '555.123456789'
   ```
3. Accept a programmatic value:
   ```php
   $analytics->setClientId('your-client-id');
   ```
4. Use a custom handler:
   ```php
   class MyClientIdHandler implements CustomClientIdHandler
   {
       public function buildClientId(): ?string
       {
           // Your custom logic to get client ID
       }
   }
   ```

### User ID

User ID helps identify logged-in users across devices. You can:

1. Set it programmatically:
   ```php
   $analytics->setUserId($user->getId());
   ```
2. Use a custom handler:
   ```php
   class MyUserIdHandler implements CustomUserIdHandler
   {
       public function buildUserId(): ?string
       {
           // Your custom logic to get user ID
       }
   }
   ```

### Session ID

Session ID helps GA4 group events that happen in the same session. The bundle can:

1. Extract it from the GA4 cookie for your property:
   ```
   // For trackingId G-NX8H4907NJ
   // Cookie format: _ga_NX8H4907NJ=GS1.1.1743566249.16.0.1743566249.60.0.181542193
   // The session ID is 1743566249 in this example
   ```
2. Accept a programmatic value:
   ```php
   $analytics->setSessionId('your-session-id');
   ```
3. Use a custom handler:
   ```php
   class MySessionIdHandler implements CustomSessionIdHandler
   {
       public function buildSessionId(): ?string
       {
           // Your custom logic to get session ID
       }
   }
   ```

## Debug Mode

Debug mode enables detailed validation of your events from GA4:

1. Configure it globally:
   ```yaml
   debug_mode: true
   ```
2. Set it programmatically:
   ```php
   $analytics->setDebugMode(true);
   ```

When debug mode is enabled:
- Events are sent to GA4's debug endpoint
- A `debug_mode` parameter is added to each event
- More detailed validation messages are returned

## License

This bundle is available under the MIT License. See the [LICENSE](LICENSE) file for more information.