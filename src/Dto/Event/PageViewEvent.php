<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Event;

/**
 * GA4 Page View event.
 */
class PageViewEvent extends AbstractEventDto
{
    /**
     * PageViewEvent constructor.
     */
    public function __construct()
    {
        parent::__construct('page_view');
    }
    
    /**
     * Set the page title.
     */
    public function setPageTitle(string $title): self
    {
        $this->addParameter('page_title', $title);
        return $this;
    }
    
    /**
     * Set the page location (URL).
     */
    public function setPageLocation(string $location): self
    {
        $this->addParameter('page_location', $location);
        return $this;
    }
    
    /**
     * Set the page referrer.
     */
    public function setPageReferrer(string $referrer): self
    {
        $this->addParameter('page_referrer', $referrer);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function validate(): bool
    {
        parent::validate();
        
        // Additional validation could be added here
        
        return true;
    }
}