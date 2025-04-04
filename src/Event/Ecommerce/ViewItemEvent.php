<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Event\Ecommerce;

use Freema\GA4MeasurementProtocolBundle\Domain\Item;
use Freema\GA4MeasurementProtocolBundle\Enum\ErrorCode;
use Freema\GA4MeasurementProtocolBundle\Event\AbstractEvent;
use Freema\GA4MeasurementProtocolBundle\Exception\ValidationException;

/**
 * View item event for GA4.
 */
class ViewItemEvent extends AbstractEvent
{
    /** @var Item[] */
    private array $items = [];

    public function getName(): string
    {
        return 'view_item';
    }

    /**
     * Set value (total).
     */
    public function setValue(float $value): self
    {
        $this->parameters['value'] = $value;

        return $this;
    }

    /**
     * Get value.
     */
    public function getValue(): ?float
    {
        if (!isset($this->parameters['value'])) {
            return null;
        }

        // @phpstan-ignore-next-line
        return is_float($this->parameters['value']) ? $this->parameters['value'] : (float) $this->parameters['value'];
    }

    /**
     * Add an item to be viewed.
     */
    public function addItem(Item $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Get all items.
     *
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Get all parameters for this event
     * Overridden to include the items array.
     */
    public function getParameters(): array
    {
        $parameters = parent::getParameters();

        // Add items if we have any
        if (!empty($this->items)) {
            $parameters['items'] = array_map(function (Item $item) {
                return $item->getParameters();
            }, $this->items);
        }

        return $parameters;
    }

    /**
     * Validate the view item event according to GA4 requirements.
     *
     * @return bool Returns true if validation passes
     *
     * @throws ValidationException If validation fails
     */
    public function validate(): bool
    {
        // Call parent validation first
        parent::validate();

        // Currency is required if value is set
        if (!empty($this->getValue())) {
            if (empty($this->getParameter('currency'))) {
                throw new ValidationException('Field "currency" is required when "value" is set', ErrorCode::VALIDATION_VIEW_ITEM_CURRENCY_REQUIRED_WITH_VALUE, 'currency');
            }
        }

        // At least one item is required
        if (empty($this->items)) {
            throw new ValidationException('At least one item is required for view_item events', ErrorCode::VALIDATION_VIEW_ITEM_ITEMS_REQUIRED, 'items');
        }

        // Validate each item
        foreach ($this->items as $index => $item) {
            try {
                $item->validate();
            } catch (ValidationException $e) {
                // Rethrow with item index in the field path
                throw new ValidationException(sprintf('Item at index %d: %s', $index, $e->getMessage()), $e->getErrorCode(), sprintf('items[%d].%s', $index, $e->getField()));
            }
        }

        return true;
    }
}
