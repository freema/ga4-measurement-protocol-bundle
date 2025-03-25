<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Request;

use Freema\GA4MeasurementProtocolBundle\Dto\ExportableInterface;

/**
 * Base abstract class for all GA4 requests.
 */
abstract class AbstractRequest implements ExportableInterface
{
    /**
     * Validate the request.
     *
     * @param string|null $context Either 'web' or 'firebase'
     * @throws \Exception
     */
    abstract public function validate(?string $context = null): bool;
}