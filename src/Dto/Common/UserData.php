<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Common;

use Freema\GA4MeasurementProtocolBundle\Dto\ExportableInterface;

class UserData implements ExportableInterface
{
    /**
     * @var UserDataItem[]
     */
    protected array $userDataItemList;

    /**
     * @var UserAddress[]|null
     */
    protected ?array $userAddressList = null;

    /**
     * UserData constructor.
     *
     * @param UserDataItem[]|null $userDataItemList
     * @param UserAddress[]|null $userAddressList
     */
    public function __construct(?array $userDataItemList = null, ?array $userAddressList = null)
    {
        $this->userDataItemList = $userDataItemList ?? [];
        $this->userAddressList = $userAddressList;
    }

    /**
     * @param UserDataItem $userDataItem
     * @return $this
     */
    public function addUserDataItem(UserDataItem $userDataItem): self
    {
        $this->userDataItemList[] = $userDataItem;
        return $this;
    }

    /**
     * @param UserAddress $userAddress
     * @return $this
     */
    public function addUserAddress(UserAddress $userAddress): self
    {
        if ($this->getUserAddressList() === null) {
            $this->setUserAddressList([]);
        }

        $this->userAddressList[] = $userAddress;
        return $this;
    }

    /**
     * @return array
     */
    public function export() : array
    {
        $userDataExport = [];
        foreach ($this->getUserDataItemList() as $userDataItem) {
            $userDataExport = array_merge($userDataExport, $userDataItem->export());
        }

        if ($this->getUserAddressList() !== null) {
            $userDataExport['address'] = array_map(function (UserAddress $userAddress) {
                return $userAddress->export();
            }, $this->getUserAddressList());
        }

        return $userDataExport;
    }

    /**
     * @return UserDataItem[]
     */
    public function getUserDataItemList() : array
    {
        return $this->userDataItemList;
    }

    /**
     * @param UserDataItem[] $userDataItemList
     * @return $this
     */
    public function setUserDataItemList(array $userDataItemList): self
    {
        $this->userDataItemList = $userDataItemList;
        return $this;
    }

    /**
     * @return UserAddress[]|null
     */
    public function getUserAddressList() : ?array
    {
        return $this->userAddressList;
    }

    /**
     * @param UserAddress[] $userAddressList
     * @return $this
     */
    public function setUserAddressList(array $userAddressList): self
    {
        $this->userAddressList = $userAddressList;
        return $this;
    }
}
