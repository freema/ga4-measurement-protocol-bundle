<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Provider;

interface CustomSessionIdHandler
{
    public function buildSessionId(): ?string;
}
