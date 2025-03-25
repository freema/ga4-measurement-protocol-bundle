<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\GA4;

use Freema\GA4MeasurementProtocolBundle\Dto\Item\ItemDto;
use Freema\GA4MeasurementProtocolBundle\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated This test class is for the legacy product parameter builder which has been replaced by ItemDto
 */
class ProductParameterBuilderTest extends TestCase
{
    private EventFactory $eventFactory;
    private ItemDto $item;

    protected function setUp(): void
    {
        $this->markTestSkipped('The ProductParameterBuilder class has been replaced with the DTO-based approach using ItemDto');
        
        // This code is just for reference and won't be executed
        $this->eventFactory = new EventFactory();
        $this->item = $this->eventFactory->createItem();
    }

    public function testBuildProductParameterWithMinimalData(): void
    {
        $product = [
            'sku' => 'SKU-123',
        ];

        $result = $this->builder->buildProductParameter($product);

        $this->assertEquals('idSKU-123', $result);
    }

    public function testBuildProductParameterWithAllData(): void
    {
        $product = [
            'sku' => 'SKU-123',
            'name' => 'Test Product',
            'brand' => 'Test Brand',
            'quantity' => 2,
            'price' => 99.99,
            'discount' => 10.00,
            'affiliation' => 'Test Store',
        ];

        $result = $this->builder->buildProductParameter($product);

        $this->assertStringContainsString('idSKU-123', $result);
        $this->assertStringContainsString('~nmTest Product', $result);
        $this->assertStringContainsString('~brTest Brand', $result);
        $this->assertStringContainsString('~qt2', $result);
        $this->assertStringContainsString('~pr99.99', $result);
        $this->assertStringContainsString('~ds10.00', $result);
        $this->assertStringContainsString('~afTest Store', $result);
    }

    public function testBuildProductParameterWithBrandButNoAffiliation(): void
    {
        $product = [
            'sku' => 'SKU-123',
            'brand' => 'Test Brand',
        ];

        $result = $this->builder->buildProductParameter($product);

        $this->assertStringContainsString('~brTest Brand', $result);
        $this->assertStringContainsString('~afTest Brand.cz', $result);
    }

    public function testBuildProductParameterWithNumericValues(): void
    {
        $product = [
            'sku' => 'SKU-123',
            'price' => 1000,  // Integer
            'discount' => 10.5,  // Float with single decimal
        ];

        $result = $this->builder->buildProductParameter($product);

        $this->assertStringContainsString('~pr1000.00', $result);  // Should be formatted as 1000.00
        $this->assertStringContainsString('~ds10.50', $result);    // Should be formatted as 10.50
    }

    public function testBuildProductParameterHandlesEmptyProduct(): void
    {
        $product = [];

        $result = $this->builder->buildProductParameter($product);

        $this->assertEquals('', $result);
    }
}
