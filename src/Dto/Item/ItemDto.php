<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Item;

use Freema\GA4MeasurementProtocolBundle\Dto\ExportableInterface;
use Freema\GA4MeasurementProtocolBundle\Dto\ValidateInterface;

/**
 * Represents a product or item in GA4 events.
 */
class ItemDto implements ExportableInterface, ValidateInterface
{
    private ?string $id = null;
    private ?string $name = null;
    private ?string $brand = null;
    private ?string $category = null;
    private ?string $variant = null;
    private ?float $price = null;
    private ?int $quantity = null;
    private ?string $coupon = null;
    private ?int $position = null;
    private ?string $affiliation = null;
    private ?float $discount = null;
    
    /**
     * Set the item ID.
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }
    
    /**
     * Get the item ID.
     */
    public function getId(): ?string
    {
        return $this->id;
    }
    
    /**
     * Set the item name.
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Get the item name.
     */
    public function getName(): ?string
    {
        return $this->name;
    }
    
    /**
     * Set the item brand.
     */
    public function setBrand(string $brand): self
    {
        $this->brand = $brand;
        return $this;
    }
    
    /**
     * Get the item brand.
     */
    public function getBrand(): ?string
    {
        return $this->brand;
    }
    
    /**
     * Set the item category.
     */
    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }
    
    /**
     * Get the item category.
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }
    
    /**
     * Set the item variant.
     */
    public function setVariant(string $variant): self
    {
        $this->variant = $variant;
        return $this;
    }
    
    /**
     * Get the item variant.
     */
    public function getVariant(): ?string
    {
        return $this->variant;
    }
    
    /**
     * Set the item price.
     */
    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }
    
    /**
     * Get the item price.
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }
    
    /**
     * Set the item quantity.
     */
    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }
    
    /**
     * Get the item quantity.
     */
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }
    
    /**
     * Set the item coupon code.
     */
    public function setCoupon(string $coupon): self
    {
        $this->coupon = $coupon;
        return $this;
    }
    
    /**
     * Get the item coupon code.
     */
    public function getCoupon(): ?string
    {
        return $this->coupon;
    }
    
    /**
     * Set the item position in a list.
     */
    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }
    
    /**
     * Get the item position in a list.
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }
    
    /**
     * Set the item affiliation.
     */
    public function setAffiliation(string $affiliation): self
    {
        $this->affiliation = $affiliation;
        return $this;
    }
    
    /**
     * Get the item affiliation.
     */
    public function getAffiliation(): ?string
    {
        return $this->affiliation;
    }
    
    /**
     * Set the item discount.
     */
    public function setDiscount(float $discount): self
    {
        $this->discount = $discount;
        return $this;
    }
    
    /**
     * Get the item discount.
     */
    public function getDiscount(): ?float
    {
        return $this->discount;
    }
    
    /**
     * {@inheritdoc}
     */
    public function export(): array
    {
        return array_filter([
            'item_id' => $this->id,
            'item_name' => $this->name,
            'item_brand' => $this->brand,
            'item_category' => $this->category,
            'item_variant' => $this->variant,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'coupon' => $this->coupon,
            'index' => $this->position,
            'item_list_name' => $this->affiliation,
            'discount' => $this->discount,
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function validate(): bool
    {
        // Either ID or name is required
        if (empty($this->id) && empty($this->name)) {
            throw new \InvalidArgumentException('Either item_id or item_name is required');
        }
        
        return true;
    }
    
    /**
     * Create an ItemDto from an array.
     */
    public static function fromArray(array $data): self
    {
        $item = new self();
        
        if (isset($data['sku']) || isset($data['item_id'])) {
            $item->setId($data['sku'] ?? $data['item_id']);
        }
        
        if (isset($data['name']) || isset($data['item_name'])) {
            $item->setName($data['name'] ?? $data['item_name']);
        }
        
        if (isset($data['brand']) || isset($data['item_brand'])) {
            $item->setBrand($data['brand'] ?? $data['item_brand']);
        }
        
        if (isset($data['category']) || isset($data['item_category'])) {
            $item->setCategory($data['category'] ?? $data['item_category']);
        }
        
        if (isset($data['variant']) || isset($data['item_variant'])) {
            $item->setVariant($data['variant'] ?? $data['item_variant']);
        }
        
        if (isset($data['price'])) {
            $item->setPrice((float)$data['price']);
        }
        
        if (isset($data['quantity'])) {
            $item->setQuantity((int)$data['quantity']);
        }
        
        if (isset($data['coupon'])) {
            $item->setCoupon($data['coupon']);
        }
        
        if (isset($data['position']) || isset($data['index'])) {
            $item->setPosition((int)($data['position'] ?? $data['index']));
        }
        
        if (isset($data['affiliation']) || isset($data['item_list_name'])) {
            $item->setAffiliation($data['affiliation'] ?? $data['item_list_name']);
        }
        
        if (isset($data['discount'])) {
            $item->setDiscount((float)$data['discount']);
        }
        
        return $item;
    }
}