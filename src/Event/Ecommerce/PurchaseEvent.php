<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Event\Ecommerce;

use Freema\GA4MeasurementProtocolBundle\Domain\Item;
use Freema\GA4MeasurementProtocolBundle\Enum\ErrorCode;
use Freema\GA4MeasurementProtocolBundle\Event\AbstractEvent;
use Freema\GA4MeasurementProtocolBundle\Exception\ValidationException;

/**
 * Purchase event for GA4.
 */
class PurchaseEvent extends AbstractEvent
{
    /** @var Item[] */
    private array $items = [];

    public function getName(): string
    {
        return 'purchase';
    }

    /**
     * Set transaction ID.
     */
    public function setTransactionId(string $transactionId): self
    {
        $this->parameters['transaction_id'] = $transactionId;

        return $this;
    }

    /**
     * Get transaction ID.
     */
    public function getTransactionId(): ?string
    {
        if (!isset($this->parameters['transaction_id'])) {
            return null;
        }

        // @phpstan-ignore-next-line
        return is_string($this->parameters['transaction_id']) ? $this->parameters['transaction_id'] : (string) $this->parameters['transaction_id'];
    }

    /**
     * Set value (revenue).
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
     * Set tax.
     */
    public function setTax(float $tax): self
    {
        $this->parameters['tax'] = $tax;

        return $this;
    }

    /**
     * Set shipping.
     */
    public function setShipping(float $shipping): self
    {
        $this->parameters['shipping'] = $shipping;

        return $this;
    }

    /**
     * Set payment type.
     */
    public function setPaymentType(string $paymentType): self
    {
        $this->parameters['payment_type'] = $paymentType;

        return $this;
    }

    /**
     * Set coupon.
     */
    public function setCoupon(string $coupon): self
    {
        $this->parameters['coupon'] = $coupon;

        return $this;
    }

    /**
     * Set affiliation.
     */
    public function setAffiliation(string $affiliation): self
    {
        $this->parameters['affiliation'] = $affiliation;

        return $this;
    }

    /**
     * Add an item to the purchase.
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
     * Validate the purchase event according to GA4 requirements.
     *
     * @return bool Returns true if validation passes
     *
     * @throws ValidationException If validation fails
     */
    public function validate(): bool
    {
        // Call parent validation first
        parent::validate();

        // Transaction ID is required
        if (empty($this->getTransactionId())) {
            throw new ValidationException('Field "transaction_id" is required for purchase events', ErrorCode::VALIDATION_PURCHASE_TRANSACTION_ID_REQUIRED, 'transaction_id');
        }

        // Currency is required if value is set
        if (!empty($this->getValue())) {
            if (empty($this->getParameter('currency'))) {
                throw new ValidationException('Field "currency" is required when "value" is set', ErrorCode::VALIDATION_PURCHASE_CURRENCY_REQUIRED_WITH_VALUE, 'currency');
            }
        }

        // At least one item is required
        if (empty($this->items)) {
            throw new ValidationException('At least one item is required for purchase events', ErrorCode::VALIDATION_PURCHASE_ITEMS_REQUIRED, 'items');
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
