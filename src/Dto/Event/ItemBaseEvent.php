<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Event;

use Freema\GA4MeasurementProtocolBundle\Dto\Parameter\ItemCollectionParameter;
use Freema\GA4MeasurementProtocolBundle\Dto\Parameter\ItemParameter;

class ItemBaseEvent extends BaseEvent
{
    /**
     * @var ItemCollectionParameter|null
     */
    protected ?ItemCollectionParameter $items = null;

    /**
     * @param ItemParameter $item
     * @return $this
     */
    public function addItem(ItemParameter $item): self
    {
        if ($this->getItems() === null) {
            $this->setItems(new ItemCollectionParameter());
        }

        $this->getItems()->addItem($item);
        return $this;
    }

    /**
     * @return ItemCollectionParameter|null
     */
    public function getItems(): ?ItemCollectionParameter
    {
        return $this->items;
    }

    /**
     * @param ItemCollectionParameter|null $items
     * @return $this
     */
    public function setItems(?ItemCollectionParameter $items): self
    {
        $this->items = $items;
        $this->setParamValue('items', $items);
        return $this;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        parent::validate();

        if ($this->getItems() !== null) {
            $this->getItems()->validate();
        }

        return true;
    }
}
