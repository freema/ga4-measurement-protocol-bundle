<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Enum;

/**
 * Consent status codes for GA4.
 */
enum ConsentCode: string
{
    /**
     * Consent granted by the user
     */
    case GRANTED = 'granted';
    
    /**
     * Consent denied by the user
     */
    case DENIED = 'denied';
}