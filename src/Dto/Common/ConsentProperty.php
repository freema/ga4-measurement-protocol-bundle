<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Common;

use Freema\GA4MeasurementProtocolBundle\Dto\ExportableInterface;
use Freema\GA4MeasurementProtocolBundle\Enum\ConsentCode;

class ConsentProperty implements ExportableInterface
{
    /**
     * @var string|null
     */
    protected ?string $adStorage = null;

    /**
     * @var string|null
     */
    protected ?string $analyticsStorage = null;

    /**
     * @var string|null
     */
    protected ?string $adUserData = null;

    /**
     * @var string|null
     */
    protected ?string $adPersonalization = null;

    /**
     * @return array
     */
    public function export(): array
    {
        $result = [];

        if ($this->getAdStorage() !== null) {
            $result['ad_storage'] = $this->getAdStorage();
        }

        if ($this->getAnalyticsStorage() !== null) {
            $result['analytics_storage'] = $this->getAnalyticsStorage();
        }

        if ($this->getAdUserData() !== null) {
            $result['ad_user_data'] = $this->getAdUserData();
        }

        if ($this->getAdPersonalization() !== null) {
            $result['ad_personalization'] = $this->getAdPersonalization();
        }

        return $result;
    }

    /**
     * @return string|null
     */
    public function getAdStorage(): ?string
    {
        return $this->adStorage;
    }

    /**
     * @param string|null $adStorage
     * @return ConsentProperty
     */
    public function setAdStorage(?string $adStorage): self
    {
        $this->adStorage = $adStorage;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAnalyticsStorage(): ?string
    {
        return $this->analyticsStorage;
    }

    /**
     * @param string|null $analyticsStorage
     * @return ConsentProperty
     */
    public function setAnalyticsStorage(?string $analyticsStorage): self
    {
        $this->analyticsStorage = $analyticsStorage;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAdUserData(): ?string
    {
        return $this->adUserData;
    }

    /**
     * @param string|null $adUserData
     * @return ConsentProperty
     */
    public function setAdUserData(?string $adUserData): self
    {
        $this->adUserData = $adUserData;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAdPersonalization(): ?string
    {
        return $this->adPersonalization;
    }

    /**
     * @param string|null $adPersonalization
     * @return ConsentProperty
     */
    public function setAdPersonalization(?string $adPersonalization): self
    {
        $this->adPersonalization = $adPersonalization;
        return $this;
    }

    /**
     * Set all consent properties to "granted".
     *
     * @return ConsentProperty
     */
    public function grantAll(): self
    {
        $this->adStorage = ConsentCode::GRANTED->value;
        $this->analyticsStorage = ConsentCode::GRANTED->value;
        $this->adUserData = ConsentCode::GRANTED->value;
        $this->adPersonalization = ConsentCode::GRANTED->value;

        return $this;
    }

    /**
     * Set all consent properties to "denied".
     *
     * @return ConsentProperty
     */
    public function denyAll(): self
    {
        $this->adStorage = ConsentCode::DENIED->value;
        $this->analyticsStorage = ConsentCode::DENIED->value;
        $this->adUserData = ConsentCode::DENIED->value;
        $this->adPersonalization = ConsentCode::DENIED->value;

        return $this;
    }
}
