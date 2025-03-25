<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Common;

use Freema\GA4MeasurementProtocolBundle\Dto\ExportableInterface;

class UserProperties implements ExportableInterface
{
    /**
     * @var UserProperty[]
     */
    protected array $userPropertiesList;

    /**
     * UserProperties constructor.
     * @param UserProperty[] $userPropertiesList
     */
    public function __construct(?array $userPropertiesList = null)
    {
        $this->userPropertiesList = $userPropertiesList ?? [];
    }

    /**
     * @param UserProperty $userProperty
     * @return $this
     */
    public function addUserProperty(UserProperty $userProperty): self
    {
        $this->userPropertiesList[] = $userProperty;
        return $this;
    }

    /**
     * @return array
     */
    public function export(): array
    {
        $result = [];
        foreach ($this->getUserPropertiesList() as $userProperty) {
            $result = array_merge($result, $userProperty->export());
        }
        return $result;
    }

    /**
     * @return UserProperty[]
     */
    public function getUserPropertiesList(): array
    {
        return $this->userPropertiesList;
    }

    /**
     * @param UserProperty[] $userPropertiesList
     * @return $this
     */
    public function setUserPropertiesList(array $userPropertiesList): self
    {
        $this->userPropertiesList = $userPropertiesList;
        return $this;
    }
}
