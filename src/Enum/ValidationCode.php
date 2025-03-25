<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Enum;

/**
 * Validation result codes.
 */
enum ValidationCode: int
{
    /**
     * Validation passed successfully
     */
    case VALIDATION_PASSED = 1;
    
    /**
     * Validation failed with errors
     */
    case VALIDATION_ERROR = 0;
}