<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto;

use Freema\GA4MeasurementProtocolBundle\Exception\ValidationException;

interface ValidateInterface
{
    /**
     * Method validates object. Throws exception if error, returns true if valid.
     * 
     * @return boolean
     * @throws ValidationException
     */
    public function validate();
}