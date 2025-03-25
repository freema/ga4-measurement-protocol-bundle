<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto;

interface ExportableInterface
{
    /**
     * Method returns prepared data
     * @return mixed
     */
    public function export();
}