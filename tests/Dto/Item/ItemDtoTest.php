<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\Dto\Item;

use Freema\GA4MeasurementProtocolBundle\Dto\Item\ItemDto;
use PHPUnit\Framework\TestCase;

class ItemDtoTest extends TestCase
{
    private ItemDto $item;

    protected function setUp(): void
    {
        $this->item = new ItemDto();
    }

    public function testId(): void
    {
        $this->assertNull($this->item->getId());
        
        $result = $this->item->setId('test-id');
        
        $this->assertSame($this->item, $result);
        $this->assertEquals('test-id', $this->item->getId());
    }
    
    public function testName(): void
    {
        $this->assertNull($this->item->getName());
        
        $result = $this->item->setName('Test Product');
        
        $this->assertSame($this->item, $result);
        $this->assertEquals('Test Product', $this->item->getName());
    }
    
    public function testBrand(): void
    {
        $this->assertNull($this->item->getBrand());
        
        $result = $this->item->setBrand('Test Brand');
        
        $this->assertSame($this->item, $result);
        $this->assertEquals('Test Brand', $this->item->getBrand());
    }
    
    public function testCategory(): void
    {
        $this->assertNull($this->item->getCategory());
        
        $result = $this->item->setCategory('Test Category');
        
        $this->assertSame($this->item, $result);
        $this->assertEquals('Test Category', $this->item->getCategory());
    }
    
    public function testVariant(): void
    {
        $this->assertNull($this->item->getVariant());
        
        $result = $this->item->setVariant('Test Variant');
        
        $this->assertSame($this->item, $result);
        $this->assertEquals('Test Variant', $this->item->getVariant());
    }
    
    public function testPrice(): void
    {
        $this->assertNull($this->item->getPrice());
        
        $result = $this->item->setPrice(99.99);
        
        $this->assertSame($this->item, $result);
        $this->assertEquals(99.99, $this->item->getPrice());
    }
    
    public function testQuantity(): void
    {
        $this->assertNull($this->item->getQuantity());
        
        $result = $this->item->setQuantity(2);
        
        $this->assertSame($this->item, $result);
        $this->assertEquals(2, $this->item->getQuantity());
    }
    
    public function testCoupon(): void
    {
        $this->assertNull($this->item->getCoupon());
        
        $result = $this->item->setCoupon('TEST10');
        
        $this->assertSame($this->item, $result);
        $this->assertEquals('TEST10', $this->item->getCoupon());
    }
    
    public function testPosition(): void
    {
        $this->assertNull($this->item->getPosition());
        
        $result = $this->item->setPosition(1);
        
        $this->assertSame($this->item, $result);
        $this->assertEquals(1, $this->item->getPosition());
    }
    
    public function testAffiliation(): void
    {
        $this->assertNull($this->item->getAffiliation());
        
        $result = $this->item->setAffiliation('Test Store');
        
        $this->assertSame($this->item, $result);
        $this->assertEquals('Test Store', $this->item->getAffiliation());
    }
    
    public function testCreativeSlot(): void
    {
        $this->assertNull($this->item->getCreativeSlot());
        
        $result = $this->item->setCreativeSlot('Test Slot');
        
        $this->assertSame($this->item, $result);
        $this->assertEquals('Test Slot', $this->item->getCreativeSlot());
    }
    
    public function testCreativeName(): void
    {
        $this->assertNull($this->item->getCreativeName());
        
        $result = $this->item->setCreativeName('Test Name');
        
        $this->assertSame($this->item, $result);
        $this->assertEquals('Test Name', $this->item->getCreativeName());
    }
    
    public function testPromotionId(): void
    {
        $this->assertNull($this->item->getPromotionId());
        
        $result = $this->item->setPromotionId('PROMO-123');
        
        $this->assertSame($this->item, $result);
        $this->assertEquals('PROMO-123', $this->item->getPromotionId());
    }
    
    public function testPromotionName(): void
    {
        $this->assertNull($this->item->getPromotionName());
        
        $result = $this->item->setPromotionName('Summer Sale');
        
        $this->assertSame($this->item, $result);
        $this->assertEquals('Summer Sale', $this->item->getPromotionName());
    }
    
    public function testLocationId(): void
    {
        $this->assertNull($this->item->getLocationId());
        
        $result = $this->item->setLocationId('loc-123');
        
        $this->assertSame($this->item, $result);
        $this->assertEquals('loc-123', $this->item->getLocationId());
    }
    
    public function testExport(): void
    {
        $this->item
            ->setId('SKU-123')
            ->setName('Test Product')
            ->setBrand('Test Brand')
            ->setCategory('Test Category')
            ->setPrice(99.99)
            ->setQuantity(2);
        
        $result = $this->item->export();
        
        $this->assertIsArray($result);
        $this->assertEquals('SKU-123', $result['item_id']);
        $this->assertEquals('Test Product', $result['item_name']);
        $this->assertEquals('Test Brand', $result['item_brand']);
        $this->assertEquals('Test Category', $result['item_category']);
        $this->assertEquals(99.99, $result['price']);
        $this->assertEquals(2, $result['quantity']);
    }
    
    public function testExportFiltersNullValues(): void
    {
        $this->item
            ->setId('SKU-123')
            ->setName('Test Product');
        
        $result = $this->item->export();
        
        $this->assertArrayHasKey('item_id', $result);
        $this->assertArrayHasKey('item_name', $result);
        $this->assertArrayNotHasKey('price', $result);
        $this->assertArrayNotHasKey('quantity', $result);
    }
    
    public function testFromArray(): void
    {
        $data = [
            'sku' => 'SKU-123',
            'name' => 'Test Product',
            'brand' => 'Test Brand',
            'price' => 99.99,
            'quantity' => 2,
        ];
        
        $item = ItemDto::fromArray($data);
        
        $this->assertEquals('SKU-123', $item->getId());
        $this->assertEquals('Test Product', $item->getName());
        $this->assertEquals('Test Brand', $item->getBrand());
        $this->assertEquals(99.99, $item->getPrice());
        $this->assertEquals(2, $item->getQuantity());
    }
}