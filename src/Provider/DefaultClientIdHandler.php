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

        // Extract client ID from _ga cookie - always take the last part
        if (isset($cookies['_ga'])) {
            $gaCookieValue = $cookies['_ga'];
            $gaParts = explode('.', $gaCookieValue);

            // Check if array is not empty before accessing last element
            return $gaParts[array_key_last($gaParts)];
        }

        // If no _ga cookie or invalid format, fall back to session ID or anonymous ID
        $sessionId = session_id();
        if (!empty($sessionId)) {
            return $sessionId;
        }

        return '555'; // Anonymous Client ID
    }
}
