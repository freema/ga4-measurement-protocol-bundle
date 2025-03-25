<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Common;

use Freema\GA4MeasurementProtocolBundle\Dto\ExportableInterface;

class UserProperty implements ExportableInterface
{
    /**
     * User property name
     * @var string|null
     */
    protected ?string $name;

    /**
     * User property value
     * @var mixed
     */
    protected $value;

    /**
     * UserProperty constructor.
     * @param string|null $name
     * @param mixed $value
     */
    public function __construct(?string $name = null, $value = null)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return array
     */
    public function export(): array
    {
        return [
            $this->getName() => [
                'value' => $this->getValue()
            ]
        ];
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return $this
     */
    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setValue($value): self
    {
        $this->value = $value;
        return $this;
    }
}
