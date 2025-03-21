<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Provider;

use Symfony\Component\HttpFoundation\RequestStack;

class DefaultClientIdHandler implements CustomClientIdHandler
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function buildClientId(): ?string
    {
        $request = $this->requestStack->getMainRequest();
        if (!$request) {
            return '555'; // Anonymous Client ID
        }

        $sessionId = session_id();
        $clientId = $request->cookies->get('_ga', $sessionId);
        if (empty($clientId)) {
            $clientId = '555'; // Anonymous Client ID.
        }

        return (string)$clientId;
    }
}
