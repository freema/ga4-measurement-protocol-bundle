<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Domain;

use Freema\GA4MeasurementProtocolBundle\Enum\ErrorCode;
use Freema\GA4MeasurementProtocolBundle\Event\ValidateInterface;
use Freema\GA4MeasurementProtocolBundle\Exception\ValidationException;

/**
 * Represents a product/item in GA4 events.
 * This is a domain object that encapsulates a product or item that can be
 * tracked in GA4 events like purchase, add_to_cart, etc.
 */
class Item implements ValidateInterface
{
    /** @var array<string, mixed> */
    private array $parameters = [];

    /**
     * Set item ID (usually the product SKU).
     */
    public function setItemId(string $id): self
    {
        $this->parameters['item_id'] = $id;

        return $this;
    }

    /**
     * Get item ID.
     *
     * @phpstan-return string|null
     */
    public function getItemId(): ?string
    {
        if (!isset($this->parameters['item_id'])) {
            return null;
        }

        /* @phpstan-ignore-next-line */
        return (string) $this->parameters['item_id'];
    }

    /**
     * Set item name.
     */
    public function setItemName(string $name): self
    {
        $this->parameters['item_name'] = $name;

        return $this;
    }

    /**
     * Get item name.
     *
     * @phpstan-return string|null
     */
    public function getItemName(): ?string
    {
        if (!isset($this->parameters['item_name'])) {
            return null;
        }

        /* @phpstan-ignore-next-line */
        return (string) $this->parameters['item_name'];
    }

    /**
     * Set item brand.
     */
    public function setItemBrand(string $brand): self
    {
        $this->parameters['item_brand'] = $brand;

        return $this;
    }

    /**
     * Get item brand.
     *
     * @phpstan-return string|null
     */
    public function getItemBrand(): ?string
    {
        if (!isset($this->parameters['item_brand'])) {
            return null;
        }

        /* @phpstan-ignore-next-line */
        return (string) $this->parameters['item_brand'];
    }

    /**
     * Set item category.
     */
    public function setItemCategory(string $category): self
    {
        $this->parameters['item_category'] = $category;

        return $this;
    }

    /**
     * Get item category.
     *
     * @phpstan-return string|null
     */
    public function getItemCategory(): ?string
    {
        if (!isset($this->parameters['item_category'])) {
            return null;
        }

        /* @phpstan-ignore-next-line */
        return (string) $this->parameters['item_category'];
    }

    /**
     * Set item price.
     */
    public function setPrice(float $price): self
    {
        $this->parameters['price'] = $price;

        return $this;
    }

    /**
     * Get price.
     *
     * @phpstan-return float|null
     */
    public function getPrice(): ?float
    {
        if (!isset($this->parameters['price'])) {
            return null;
        }

        /* @phpstan-ignore-next-line */
        return (float) $this->parameters['price'];
    }

    /**
     * Set item quantity.
     */
    public function setQuantity(int $quantity): self
    {
        $this->parameters['quantity'] = $quantity;

        return $this;
    }

    /**
     * Get quantity.
     *
     * @phpstan-return int|null
     */
    public function getQuantity(): ?int
    {
        if (!isset($this->parameters['quantity'])) {
            return null;
        }

        /* @phpstan-ignore-next-line */
        return (int) $this->parameters['quantity'];
    }

    /**
     * Set item discount.
     */
    public function setDiscount(float $discount): self
    {
        $this->parameters['discount'] = $discount;

        return $this;
    }

    /**
     * Get item discount.
     *
     * @phpstan-return float|null
     */
    public function getDiscount(): ?float
    {
        if (!isset($this->parameters['discount'])) {
            return null;
        }

        /* @phpstan-ignore-next-line */
        return (float) $this->parameters['discount'];
    }

    /**
     * Add custom parameter to the item.
     */
    public function addParameter(string $key, mixed $value): self
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    /**
     * Get parameter by key.
     *
     * @return mixed The parameter value or null if not set
     */
    public function getParameter(string $key)
    {
        return $this->parameters[$key] ?? null;
    }

    /**
     * Get all parameters for this item.
     *
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Validate the item according to GA4 requirements.
     *
     * @return bool Returns true if validation passes
     *
     * @throws ValidationException If validation fails
     */
    public function validate(): bool
    {
        // At least one of item_id or item_name is required
        if (empty($this->getItemId()) && empty($this->getItemName())) {
            throw new ValidationException('At least one of item_id or item_name is required', ErrorCode::VALIDATION_ITEM_AT_LEAST_ITEM_ID_OR_ITEM_NAME_REQUIRED, 'item_id|item_name');
        }

        // Price is required
        if (null === $this->getPrice()) {
            throw new ValidationException('Field "price" is required for item', ErrorCode::VALIDATION_ITEM_PRICE_REQUIRED, 'price');
        }

        // Quantity is required
        if (null === $this->getQuantity()) {
            throw new ValidationException('Field "quantity" is required for item', ErrorCode::VALIDATION_ITEM_QUANTITY_REQUIRED, 'quantity');
        }

        return true;
    }

    /**
     * Create an item from an array of parameters.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $item = new self();

        if (isset($data['item_id']) || isset($data['sku'])) {
            /* @phpstan-ignore-next-line */
            $item->setItemId((string) ($data['item_id'] ?? $data['sku']));
        }

        if (isset($data['item_name']) || isset($data['name'])) {
            /* @phpstan-ignore-next-line */
            $item->setItemName((string) ($data['item_name'] ?? $data['name']));
        }

        if (isset($data['item_brand']) || isset($data['brand'])) {
            /* @phpstan-ignore-next-line */
            $item->setItemBrand((string) ($data['item_brand'] ?? $data['brand']));
        }

        if (isset($data['item_category']) || isset($data['category'])) {
            /* @phpstan-ignore-next-line */
            $item->setItemCategory((string) ($data['item_category'] ?? $data['category']));
        }

        if (isset($data['price'])) {
            /* @phpstan-ignore-next-line */
            $item->setPrice((float) $data['price']);
        }

        if (isset($data['quantity'])) {
            /* @phpstan-ignore-next-line */
            $item->setQuantity((int) $data['quantity']);
        }

        if (isset($data['discount'])) {
            /* @phpstan-ignore-next-line */
            $item->setDiscount((float) $data['discount']);
        }

        // Add any additional parameters
        foreach ($data as $key => $value) {
            // Skip already handled fields
            if (!in_array($key, ['item_id', 'sku', 'item_name', 'name', 'item_brand', 'brand',
                'item_category', 'category', 'price', 'quantity', 'discount'])) {
                $item->addParameter($key, $value);
            }
        }

        return $item;
    }

    /**
     * Convert the object to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->parameters;
    }

    /**
     * Convert the object to a JSON string.
     */
    public function toJson(): string
    {
        $json = json_encode($this->parameters, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return false === $json ? '{}' : $json;
    }
}
