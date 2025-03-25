<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Common;

use Freema\GA4MeasurementProtocolBundle\Dto\ExportableInterface;

class UserAddress implements ExportableInterface
{
    /**
     * @var UserDataItem[]|null
     */
    protected ?array $userAddressItemList = null;

    /**
     * UserAddress constructor.
     */
    public function __construct(?array $userAddressItemList = null)
    {
        $this->userAddressItemList = $userAddressItemList;
    }

    /**
     * @param UserDataItem $userAddressItem
     * @return $this
     */
    public function addUserAddressItem(UserDataItem $userAddressItem): self
    {
        if ($this->userAddressItemList === null) {
            $this->userAddressItemList = [];
        }
        $this->userAddressItemList[] = $userAddressItem;
        return $this;
    }

    /**
     * @return array
     */
    public function export() : array
    {
        if ($this->userAddressItemList === null) {
            return [];
        }
        
        $userAddressExport = [];
        foreach ($this->getUserAddressItemList() as $userAddressItem) {
            $userAddressExport = array_merge($userAddressExport, $userAddressItem->export());
        }

        return $userAddressExport;
    }

    /**
     * @return UserDataItem[]|null
     */
    public function getUserAddressItemList() : ?array
    {
        return $this->userAddressItemList;
    }

    /**
     * @param UserDataItem[] $userAddressItemList
     * @return $this
     */
    public function setUserAddressItemList(array $userAddressItemList): self
    {
        $this->userAddressItemList = $userAddressItemList;
        return $this;
    }
}
