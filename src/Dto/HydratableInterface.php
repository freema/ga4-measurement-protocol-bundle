<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto;

use Freema\GA4MeasurementProtocolBundle\Exception\HydrationException;
use Psr\Http\Message\ResponseInterface;

interface HydratableInterface
{
    /**
     * Method hydrates DTO with data from blueprint
     * @param ResponseInterface|array $blueprint
     * @throws HydrationException
     */
    public function hydrate($blueprint);
}