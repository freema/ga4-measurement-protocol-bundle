<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\GA4;

use Freema\GA4MeasurementProtocolBundle\GA4\AnalyticsGA4Data;
use Freema\GA4MeasurementProtocolBundle\GA4\ParameterBuilder;
use Freema\GA4MeasurementProtocolBundle\GA4\ProductParameterBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ParameterBuilderTest extends TestCase
{
    private ProductParameterBuilder|MockObject $productBuilder;
    private RequestStack|MockObject $requestStack;
    private ParameterBuilder $builder;
    private AnalyticsGA4Data $data;

    protected function setUp(): void
    {
        $this->productBuilder = $this->createMock(ProductParameterBuilder::class);
        $this->requestStack = $this->createMock(RequestStack::class);

        $this->builder = new ParameterBuilder(
            $this->productBuilder,
            $this->requestStack,
            'https://test.analytics.com/g/collect'
        );

        $this->data = new AnalyticsGA4Data();
        $this->data->setTrackingId('G-TEST123');
        $this->data->setClientId('123.456');
        $this->data->setEventName('page_view');
    }

    public function testBuildParametersWithBasicData(): void
    {
        $result = $this->builder->buildParameters($this->data);

        $this->assertArrayHasKey('params', $result);
        $this->assertArrayHasKey('url', $result);

        $params = $result['params'];
        $this->assertEquals('2', $params['v']);
        $this->assertEquals('G-TEST123', $params['tid']);
        $this->assertEquals('123.456', $params['cid']);
        $this->assertEquals('page_view', $params['en']);
        $this->assertEquals('mp', $params['ep.platform']);
        $this->assertEquals('page_view', $params['ep.page_type']);

        $this->assertStringContainsString('https://test.analytics.com/g/collect?', $result['url']);
        $this->assertStringContainsString('v=2', $result['url']);
        $this->assertStringContainsString('tid=G-TEST123', $result['url']);
        $this->assertStringContainsString('cid=123.456', $result['url']);
    }

    public function testBuildParametersWithPurchaseData(): void
    {
        $this->data->setEventName('purchase');
        $this->data->setTransactionId('TX-123');
        $this->data->setRevenue(99.99);
        $this->data->setTax(10.0);
        $this->data->setShipping(5.0);
        $this->data->setDiscount(2.0);
        $this->data->setAffiliation('Test Store');
        $this->data->setPaymentType('Credit Card');
        $this->data->setShippingTier('Standard');

        $result = $this->builder->buildParameters($this->data);
        $params = $result['params'];

        $this->assertEquals('purchase', $params['en']);
        $this->assertEquals('TX-123', $params['ep.transaction_id']);
        $this->assertEquals('Test Store', $params['ep.affiliation']);
        $this->assertEquals('99.99', $params['epn.value']);
        $this->assertEquals('99.99', $params['epn.payment']);
        $this->assertEquals('10.00', $params['epn.tax']);
        $this->assertEquals('5.00', $params['epn.shipping']);
        $this->assertEquals('2.00', $params['epn.discount']);
        $this->assertEquals('Credit Card', $params['ep.payment_type']);
        $this->assertEquals('Standard', $params['ep.shipping_tier']);
    }

    public function testBuildParametersWithProducts(): void
    {
        $product1 = ['sku' => 'SKU-1', 'name' => 'Product 1', 'price' => 10.99];
        $product2 = ['sku' => 'SKU-2', 'name' => 'Product 2', 'price' => 20.99];

        $this->data->addProduct($product1);
        $this->data->addProduct($product2);

        $this->productBuilder
            ->expects($this->exactly(2))
            ->method('buildProductParameter')
            ->willReturnMap([
                [$product1, 'idSKU-1~nmProduct 1~pr10.99'],
                [$product2, 'idSKU-2~nmProduct 2~pr20.99'],
            ]);

        $result = $this->builder->buildParameters($this->data);
        $params = $result['params'];

        $this->assertEquals('idSKU-1~nmProduct 1~pr10.99', $params['pr1']);
        $this->assertEquals('idSKU-2~nmProduct 2~pr20.99', $params['pr2']);
    }

    public function testBuildParametersWithCustomEndpoint(): void
    {
        $customEndpoint = 'https://custom.analytics.com/g/collect';

        $result = $this->builder->buildParameters($this->data, $customEndpoint);

        $this->assertStringStartsWith($customEndpoint, $result['url']);
    }

    public function testBuildParametersWithRequestUrl(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getSchemeAndHttpHost')->willReturn('https://example.com');

        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->data->setDocumentPath('/test-page');

        $result = $this->builder->buildParameters($this->data);
        $params = $result['params'];

        $this->assertEquals('https://example.com/test-page', $params['dl']);
    }

    public function testBuildParametersWithCustomParameters(): void
    {
        $this->data->addCustomParameter('custom_key1', 'value1');
        $this->data->addCustomParameter('custom_key2', 'value2');

        $result = $this->builder->buildParameters($this->data);
        $params = $result['params'];

        $this->assertEquals('value1', $params['custom_key1']);
        $this->assertEquals('value2', $params['custom_key2']);
    }
}
