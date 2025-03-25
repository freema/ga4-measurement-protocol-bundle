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
            return null;
        }

        // Get all cookies
        $cookies = [];
        $cookieHeader = $request->headers->get('cookie');
        if ($cookieHeader) {
            $cookieParts = explode('; ', $cookieHeader);
            foreach ($cookieParts as $cookie) {
                if (false !== strpos($cookie, '=')) {
                    list($name, $value) = explode('=', $cookie, 2);
                    $cookies[$name] = $value;
                }
            }
        }

        // Extract client ID from _ga cookie (opravil jsem '_ga' místo '*ga')
        if (isset($cookies['_ga'])) {
            $gaCookieValue = $cookies['_ga'];
            $gaParts = explode('.', $gaCookieValue);

            // Format is GA1.2.XXXXXXXXXX.YYYYYYYYYY
            if (count($gaParts) >= 4) {
                return $gaParts[count($gaParts) - 2].'.'.$gaParts[count($gaParts) - 1];
            }
        }

        // If no _ga cookie or invalid format, fall back to session ID or anonymous ID
        $sessionId = session_id();
        if (!empty($sessionId)) {
            return $sessionId;
        }

        return null;
    }
}
