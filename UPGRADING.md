# Upgrading Guide

This document describes breaking changes and how to upgrade from one version to another.

## Upgrading from v1.x to v2.0

Version 2.0 introduces a completely revamped API with an event-based architecture. This is a significant departure from the previous approach.

### Key Changes

1. **Event-Based Architecture**
   - Events are now separate objects that encapsulate their data
   - The API has been simplified to `addEvent()` and `send()`
   - No more setting properties on the analytics client directly

2. **Registry Changes**
   - `AnalyticsRegistryInterface::getAnalytics()` has been replaced with `getClient()`
   - Return type is now `AnalyticsClientInterface` instead of `AnalyticsGA4`

3. **Client ID Handling**
   - No longer generates random UUIDs as a fallback
   - Will throw an exception if no client ID is available

4. **No more Br33f dependency**
   - Removed dependency on the Br33f library

### Upgrading Your Code

#### Using the New API

**Before**:
```php
$analytics = $analyticsRegistry->getAnalytics('front');
$analytics->setDocumentPath('/your-page-path');
$analytics->setDocumentTitle('Your Page Title');
$analytics->sendPageview();
```

**After**:
```php
$analytics = $analyticsRegistry->getClient('front');

$pageViewEvent = new PageViewEvent();
$pageViewEvent->setDocumentPath('/your-page-path');
$pageViewEvent->setDocumentTitle('Your Page Title');

$analytics->addEvent($pageViewEvent);
$analytics->send();
```

#### Ecommerce Tracking

**Before**:
```php
$analytics = $analyticsRegistry->getAnalytics('front');
$analytics->setProductActionToPurchase();
$analytics->setTransactionId($order->getId());
$analytics->setRevenue($order->getTotal());

foreach ($order->getItems() as $item) {
    $analytics->addProduct([
        'sku' => $item->getSku(),
        'name' => $item->getName(),
        'price' => $item->getPrice(),
        'quantity' => $item->getQuantity(),
    ]);
}

$analytics->sendEvent();
```

**After**:
```php
$analytics = $analyticsRegistry->getClient('front');

$purchaseEvent = new PurchaseEvent();
$purchaseEvent->setTransactionId($order->getId());
$purchaseEvent->setValue($order->getTotal());

foreach ($order->getItems() as $item) {
    $product = new Item();
    $product->setItemId($item->getSku());
    $product->setItemName($item->getName());
    $product->setPrice($item->getPrice());
    $product->setQuantity($item->getQuantity());
    
    $purchaseEvent->addItem($product);
}

$analytics->addEvent($purchaseEvent);
$analytics->send();
```

#### Custom Parameters

**Before**:
```php
$analytics->addCustomParameter('category', 'user_engagement');
$analytics->addCustomParameter('action', 'click');
```

**After**:
```php
$event->addParameter('category', 'user_engagement');
$event->addParameter('action', 'click');
```

### Client ID Management

In v2.0, you must ensure a client ID is available either by:

1. Setting it manually: `$analytics->setClientId('your-client-id')`
2. Configuring it in YAML: `client_id: 'your-client-id'`
3. Implementing a custom client ID handler that reliably returns a client ID
4. Ensuring cookies are properly configured for the default handler

If no client ID can be determined, an exception will be thrown.

## Additional Resources

For more detailed information on using the new API, refer to the [README.md](README.md) file.