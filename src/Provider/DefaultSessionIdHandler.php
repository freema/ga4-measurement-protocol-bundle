<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Provider;

use Symfony\Component\HttpFoundation\RequestStack;

class DefaultSessionIdHandler implements SessionIdHandler
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function buildSessionId(): ?string
    {
        $request = $this->requestStack->getMainRequest();
        if (!$request) {
            $sid = session_id();
            return $sid !== false ? $sid : null;
        }

        // Try to get GA session ID from cookies
        $sessionId = $request->cookies->get('_ga_session_id');

        // If not found, try to extract from _ga cookie
        if (!$sessionId) {
            $gaCookie = $request->cookies->get('_ga');
            if ($gaCookie && is_string($gaCookie) && preg_match('/\d+\.\d+$/', $gaCookie, $matches)) {
                $sessionId = $matches[0];
            }
        }

        // If still not found, use session ID
        if (!$sessionId) {
            $sid = session_id();
            $sessionId = $sid !== false ? $sid : null;
        }

        return $sessionId;
    }
}
