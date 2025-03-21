<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\GA4;

use Freema\GA4MeasurementProtocolBundle\GA4\AnalyticsGA4Data;
use PHPUnit\Framework\TestCase;

class AnalyticsGA4DataTest extends TestCase
{
    private AnalyticsGA4Data $data;

    protected function setUp(): void
    {
        $this->data = new AnalyticsGA4Data();
    }

    public function testBasicProperties(): void
    {
        $this->data->setTrackingId('G-TEST123');
        $this->data->setProtocolVersion('2');
        $this->data->setClientId('123.456');
        $this->data->setUserId('user123');
        $this->data->setUserAgent('Test User Agent');

        $this->assertEquals('G-TEST123', $this->data->getTrackingId());
        $this->assertEquals('2', $this->data->getProtocolVersion());
        $this->assertEquals('123.456', $this->data->getClientId());
        $this->assertEquals('user123', $this->data->getUserId());
        $this->assertEquals('Test User Agent', $this->data->getUserAgent());
    }

    public function testDocumentProperties(): void
    {
        $this->data->setDocumentPath('/test-path');
        $this->data->setDocumentReferrer('https://referrer.com');
        $this->data->setDocumentTitle('Test Title');

        $this->assertEquals('/test-path', $this->data->getDocumentPath());
        $this->assertEquals('https://referrer.com', $this->data->getDocumentReferrer());
        $this->assertEquals('Test Title', $this->data->getDocumentTitle());
    }

    public function testSessionId(): void
    {
        $this->data->setSessionId('test-session-id');
        $this->assertEquals('test-session-id', $this->data->getSessionId());
    }

    public function testEcommerceProperties(): void
    {
        $this->data->setCurrency('USD');
        $this->data->setTransactionId('TX-123');
        $this->data->setRevenue(99.99);
        $this->data->setTax(10.0);
        $this->data->setShipping(5.0);
        $this->data->setDiscount(2.0);
        $this->data->setAffiliation('Test Store');
        $this->data->setPaymentType('Credit Card');
        $this->data->setShippingTier('Standard');

        $this->assertEquals('USD', $this->data->getCurrency());
        $this->assertEquals('TX-123', $this->data->getTransactionId());
        $this->assertEquals(99.99, $this->data->getRevenue());
        $this->assertEquals(10.0, $this->data->getTax());
        $this->assertEquals(5.0, $this->data->getShipping());
        $this->assertEquals(2.0, $this->data->getDiscount());
        $this->assertEquals('Test Store', $this->data->getAffiliation());
        $this->assertEquals('Credit Card', $this->data->getPaymentType());
        $this->assertEquals('Standard', $this->data->getShippingTier());
    }

    public function testProducts(): void
    {
        $product1 = ['sku' => 'SKU-1', 'name' => 'Product 1'];
        $product2 = ['sku' => 'SKU-2', 'name' => 'Product 2'];

        $this->data->addProduct($product1);
        $this->data->addProduct($product2);

        $products = $this->data->getProducts();

        $this->assertCount(2, $products);
        $this->assertEquals($product1, $products[0]);
        $this->assertEquals($product2, $products[1]);
    }

    public function testCustomParameters(): void
    {
        $this->data->addCustomParameter('custom_key1', 'value1');
        $this->data->addCustomParameter('custom_key2', 'value2');

        $customParams = $this->data->getCustomParameters();

        $this->assertCount(2, $customParams);
        $this->assertEquals('value1', $customParams['custom_key1']);
        $this->assertEquals('value2', $customParams['custom_key2']);
    }

    public function testEventName(): void
    {
        // Default event name
        $this->assertEquals('custom_event', $this->data->getEventName());

        // Custom event name
        $this->data->setEventName('test_event');
        $this->assertEquals('test_event', $this->data->getEventName());
    }

    public function testEventCategoryAndAction(): void
    {
        $this->data->setEventCategory('test_category');
        $this->data->setEventAction('test_action');

        $customParams = $this->data->getCustomParameters();

        $this->assertEquals('test_category', $customParams['ep.category']);
        $this->assertEquals('test_action', $customParams['ep.action']);
    }

    public function testSetProductActionToPurchase(): void
    {
        $this->data->setProductActionToPurchase();

        $this->assertEquals('purchase', $this->data->getEventName());
    }

    public function testFluidInterface(): void
    {
        $result = $this->data
            ->setTrackingId('G-TEST123')
            ->setClientId('123.456')
            ->setEventName('test_event');

        $this->assertSame($this->data, $result);
    }
}
