<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Parameter;

use Freema\GA4MeasurementProtocolBundle\Dto\ExportableInterface;
use Freema\GA4MeasurementProtocolBundle\Dto\ValidateInterface;
use Freema\GA4MeasurementProtocolBundle\Exception\ValidationException;

class ItemCollectionParameter implements ExportableInterface, ValidateInterface
{
    /**
     * @var ItemParameter[]
     */
    protected array $items = [];

    /**
     * @param ItemParameter $item
     * @return $this
     */
    public function addItem(ItemParameter $item): self
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * @return ItemParameter[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param ItemParameter[] $items
     * @return $this
     */
    public function setItems(array $items): self
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @return array
     */
    public function export(): array
    {
        return array_map(function (ItemParameter $item) {
            return $item->export();
        }, $this->getItems());
    }

    /**
     * @return bool
     * @throws ValidationException
     */
    public function validate(): bool
    {
        foreach ($this->getItems() as $item) {
            $item->validate();
        }

        return true;
    }
}
