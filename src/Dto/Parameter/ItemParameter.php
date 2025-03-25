<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Parameter;

class ItemParameter extends BaseParameter
{
    /**
     * @var string|null
     */
    protected ?string $id = null;

    /**
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * @var string|null
     */
    protected ?string $brand = null;

    /**
     * @var string|null
     */
    protected ?string $category = null;
    
    /**
     * @var string|null
     */
    protected ?string $variant = null;
    
    /**
     * @var float|null
     */
    protected ?float $price = null;
    
    /**
     * @var int|null
     */
    protected ?int $quantity = null;
    
    /**
     * @var string|null
     */
    protected ?string $coupon = null;
    
    /**
     * @var int|null
     */
    protected ?int $position = null;
    
    /**
     * @var string|null
     */
    protected ?string $affiliation = null;

    /**
     * @var float|null
     */
    protected ?float $discount = null;

    /**
     * @return array
     */
    public function export(): array
    {
        return array_filter([
            'item_id' => $this->getId(),
            'item_name' => $this->getName(),
            'item_brand' => $this->getBrand(),
            'item_category' => $this->getCategory(),
            'item_variant' => $this->getVariant(),
            'price' => $this->getPrice(),
            'quantity' => $this->getQuantity(),
            'coupon' => $this->getCoupon(),
            'index' => $this->getPosition(),
            'item_list_name' => $this->getAffiliation(),
            'discount' => $this->getDiscount(),
        ]);
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return ItemParameter
     */
    public function setId(?string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return ItemParameter
     */
    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBrand(): ?string
    {
        return $this->brand;
    }

    /**
     * @param string|null $brand
     * @return ItemParameter
     */
    public function setBrand(?string $brand): self
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @param string|null $category
     * @return ItemParameter
     */
    public function setCategory(?string $category): self
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getVariant(): ?string
    {
        return $this->variant;
    }

    /**
     * @param string|null $variant
     * @return ItemParameter
     */
    public function setVariant(?string $variant): self
    {
        $this->variant = $variant;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param float|null $price
     * @return ItemParameter
     */
    public function setPrice(?float $price): self
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    /**
     * @param int|null $quantity
     * @return ItemParameter
     */
    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCoupon(): ?string
    {
        return $this->coupon;
    }

    /**
     * @param string|null $coupon
     * @return ItemParameter
     */
    public function setCoupon(?string $coupon): self
    {
        $this->coupon = $coupon;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @param int|null $position
     * @return ItemParameter
     */
    public function setPosition(?int $position): self
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAffiliation(): ?string
    {
        return $this->affiliation;
    }

    /**
     * @param string|null $affiliation
     * @return ItemParameter
     */
    public function setAffiliation(?string $affiliation): self
    {
        $this->affiliation = $affiliation;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getDiscount(): ?float
    {
        return $this->discount;
    }

    /**
     * @param float|null $discount
     * @return ItemParameter
     */
    public function setDiscount(?float $discount): self
    {
        $this->discount = $discount;
        return $this;
    }
}
