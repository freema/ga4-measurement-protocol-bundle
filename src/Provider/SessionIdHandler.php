<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Provider;

interface SessionIdHandler
{
    public function buildSessionId(): ?string;
}
