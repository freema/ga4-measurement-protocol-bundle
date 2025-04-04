<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Event;

use Freema\GA4MeasurementProtocolBundle\Enum\ErrorCode;
use Freema\GA4MeasurementProtocolBundle\Exception\ValidationException;

/**
 * Page view event for GA4.
 */
class PageViewEvent extends AbstractEvent
{
    public function getName(): string
    {
        return 'page_view';
    }

    /**
     * Validate the page view event according to GA4 requirements.
     *
     * @return bool Returns true if validation passes
     *
     * @throws ValidationException If validation fails
     */
    public function validate(): bool
    {
        // Call parent validation first
        parent::validate();

        // At least one of page_location or page_title is required
        if (empty($this->getParameter('page_location')) && empty($this->getParameter('page_title'))) {
            throw new ValidationException('At least one of page_location or page_title is required', ErrorCode::VALIDATION_PAGE_VIEW_LOCATION_OR_TITLE_REQUIRED, 'page_location|page_title');
        }

        return true;
    }
}
