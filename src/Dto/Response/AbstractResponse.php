<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Response;

use Freema\GA4MeasurementProtocolBundle\Dto\ExportableInterface;
use Freema\GA4MeasurementProtocolBundle\Dto\HydratableInterface;

abstract class AbstractResponse implements ExportableInterface, HydratableInterface
{
}
