<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Event;

use Freema\GA4MeasurementProtocolBundle\Dto\Item\ItemDto;
use Freema\GA4MeasurementProtocolBundle\Exception\ValidationException;

/**
 * GA4 Purchase event.
 */
class PurchaseEvent extends AbstractEventDto
{
    /**
     * @var ItemDto[]
     */
    private array $items = [];
    
    /**
     * PurchaseEvent constructor.
     */
    public function __construct()
    {
        parent::__construct('purchase');
    }
    
    /**
     * Set the transaction ID.
     */
    public function setTransactionId(string $transactionId): self
    {
        $this->addParameter('transaction_id', $transactionId);
        return $this;
    }
    
    /**
     * Set the currency code (ISO 4217).
     */
    public function setCurrency(string $currency): self
    {
        $this->addParameter('currency', $currency);
        return $this;
    }
    
    /**
     * Set the transaction value/revenue.
     */
    public function setValue(float $value): self
    {
        $this->addParameter('value', $value);
        return $this;
    }
    
    /**
     * Set the transaction tax.
     */
    public function setTax(float $tax): self
    {
        $this->addParameter('tax', $tax);
        return $this;
    }
    
    /**
     * Set the shipping amount.
     */
    public function setShipping(float $shipping): self
    {
        $this->addParameter('shipping', $shipping);
        return $this;
    }
    
    /**
     * Set the affiliation (store or dealer).
     */
    public function setAffiliation(string $affiliation): self
    {
        $this->addParameter('affiliation', $affiliation);
        return $this;
    }
    
    /**
     * Set the coupon code.
     */
    public function setCoupon(string $coupon): self
    {
        $this->addParameter('coupon', $coupon);
        return $this;
    }
    
    /**
     * Add an item to the purchase.
     */
    public function addItem(ItemDto $item): self
    {
        $this->items[] = $item;
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function export(): array
    {
        $result = parent::export();
        
        // Add items array if there are any items
        if (!empty($this->items)) {
            $itemsArray = [];
            foreach ($this->items as $item) {
                $itemsArray[] = $item->export();
            }
            $result['params']->offsetSet('items', $itemsArray);
        }
        
        return $result;
    }
    
    /**
     * {@inheritdoc}
     */
    public function validate(): bool
    {
        parent::validate();
        
        // Transaction ID is required
        if (empty($this->parameters['transaction_id'] ?? null)) {
            throw new ValidationException('Field "transaction_id" is required for purchase events');
        }
        
        // Currency is required if value is set
        if (isset($this->parameters['value']) && empty($this->parameters['currency'] ?? null)) {
            throw new ValidationException('Field "currency" is required when "value" is set');
        }
        
        // Validate all items
        foreach ($this->items as $item) {
            $item->validate();
        }
        
        return true;
    }
}