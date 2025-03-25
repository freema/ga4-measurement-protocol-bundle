<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Enum;

/**
 * Error codes used in validation exceptions.
 */
enum ErrorCode: int
{
    /**
     * Generic error code for required field validation
     */
    case VALIDATION_FIELD_REQUIRED = 1000;
    
    /**
     * Error code for when client_id is required but not provided
     */
    case VALIDATION_CLIENT_ID_REQUIRED = 1001;
    
    /**
     * Error code for when app_instance_id is required but not provided
     */
    case VALIDATION_APP_INSTANCE_ID_REQUIRED = 1002;
    
    /**
     * Error code for when both client_id and app_instance_id are provided
     */
    case VALIDATION_CLIENT_IDENTIFIER_MISCONFIGURED = 1003;
    
    /**
     * Error code for when event name is required but not provided
     */
    case VALIDATION_EVENT_NAME_REQUIRED = 1100;
    
    /**
     * Error code for when events collection must not be empty
     */
    case VALIDATION_EVENTS_MUST_NOT_BE_EMPTY = 1200;
    
    /**
     * Error code for when event count exceeds maximum (25)
     */
    case MAX_EVENT_COUNT_EXCEED = 1300;
}