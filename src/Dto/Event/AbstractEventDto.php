<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Event;

use Freema\GA4MeasurementProtocolBundle\Dto\ExportableInterface;

/**
 * Base abstract class for all GA4 events.
 */
abstract class AbstractEventDto implements EventInterface
{
    /**
     * @var array<string, mixed>
     */
    protected array $parameters = [];
    
    /**
     * AbstractEventDto constructor.
     */
    public function __construct(
        protected string $name
    ) {
    }
    
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * {@inheritdoc}
     */
    public function addParameter(string $name, mixed $value): self
    {
        $this->parameters[$name] = $value;
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
    
    /**
     * {@inheritdoc}
     */
    public function export(): array
    {
        $exportedParams = [];
        
        foreach ($this->parameters as $key => $value) {
            if ($value instanceof ExportableInterface) {
                $exportedParams[$key] = $value->export();
            } else {
                $exportedParams[$key] = $value;
            }
        }
        
        return [
            'name' => $this->getName(),
            'params' => new \ArrayObject($exportedParams),
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function validate(): bool
    {
        // Basic validation - can be overridden by child classes
        if (empty($this->getName())) {
            throw new \InvalidArgumentException('Event name cannot be empty');
        }
        
        return true;
    }
}