<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Parameter;

use Freema\GA4MeasurementProtocolBundle\Dto\ExportableInterface;
use Freema\GA4MeasurementProtocolBundle\Dto\ValidateInterface;

abstract class AbstractParameter implements ExportableInterface, ValidateInterface
{
}
