<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Provider;

interface CustomClientIdHandler
{
    public function buildClientId(): ?string;
}
