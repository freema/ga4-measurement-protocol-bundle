<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Enum;

/**
 * Error codes for validation exceptions.
 */
class ErrorCode
{
    // General validation errors
    public const VALIDATION_FIELD_REQUIRED = 1001;
    public const VALIDATION_FIELD_INVALID = 1002;
    public const VALIDATION_FIELD_TYPE_MISMATCH = 1003;
    public const VALIDATION_FIELD_RANGE_ERROR = 1004;

    // Item validation errors
    public const VALIDATION_ITEM_AT_LEAST_ITEM_ID_OR_ITEM_NAME_REQUIRED = 2001;
    public const VALIDATION_ITEM_PRICE_REQUIRED = 2002;
    public const VALIDATION_ITEM_QUANTITY_REQUIRED = 2003;

    // Purchase event validation errors
    public const VALIDATION_PURCHASE_TRANSACTION_ID_REQUIRED = 3001;
    public const VALIDATION_PURCHASE_CURRENCY_REQUIRED_WITH_VALUE = 3002;
    public const VALIDATION_PURCHASE_ITEMS_REQUIRED = 3003;

    // Page view validation errors
    public const VALIDATION_PAGE_VIEW_LOCATION_OR_TITLE_REQUIRED = 4001;

    // Add to cart validation errors
    public const VALIDATION_ADD_TO_CART_ITEMS_REQUIRED = 5001;
    public const VALIDATION_ADD_TO_CART_CURRENCY_REQUIRED_WITH_VALUE = 5002;

    // View item validation errors
    public const VALIDATION_VIEW_ITEM_ITEMS_REQUIRED = 6001;
    public const VALIDATION_VIEW_ITEM_CURRENCY_REQUIRED_WITH_VALUE = 6002;

    // Begin checkout validation errors
    public const VALIDATION_BEGIN_CHECKOUT_ITEMS_REQUIRED = 7001;
    public const VALIDATION_BEGIN_CHECKOUT_CURRENCY_REQUIRED_WITH_VALUE = 7002;
}
