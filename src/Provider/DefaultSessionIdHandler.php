<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Provider;

use Symfony\Component\HttpFoundation\RequestStack;

class DefaultSessionIdHandler implements CustomSessionIdHandler
{
    private ?string $trackingId = null;

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * Set the tracking ID to help find the right GA cookie.
     */
    public function setTrackingId(string $trackingId): void
    {
        // Remove the 'G-' prefix if present
        $this->trackingId = str_replace('G-', '', $trackingId);
    }

    public function buildSessionId(): ?string
    {
        $request = $this->requestStack->getMainRequest();
        if (!$request) {
            return null;
        }

        // If tracking ID is set, try to find the specific GA cookie for this property
        if ($this->trackingId) {
            $gaCookieName = '_ga_'.$this->trackingId;
            if ($request->cookies->has($gaCookieName)) {
                $gaCookie = $request->cookies->get($gaCookieName);
                $gaCookie = (string) $gaCookie;

                // The cookie value gets transformed by PHP/Symfony in some environments
                // Original format: GS1.1.1757570945.16.0.1757570945.60.0.181542193
                // PHP may see: GS2.1.s1757570945$o1$g0$t1757570949$j56$l0$h1734790078
                // The session ID is prefixed with 's' and followed by '$' in transformed format

                // First, try to extract session ID from transformed format (s prefix pattern)
                if (preg_match('/s(\d+)\$/', $gaCookie, $matches)) {
                    // Return just the numeric session ID without the 's' prefix
                    return $matches[1];
                }

                // Try standard GA4 format parsing
                $parts = explode('.', $gaCookie);
                if (count($parts) >= 3) {
                    // Check if third part starts with 's' and extract number
                    if (preg_match('/^s(\d+)/', $parts[2], $matches)) {
                        return $matches[1];
                    }
                    // Return as-is if it's already a number (standard format)
                    if (is_numeric($parts[2])) {
                        return $parts[2];
                    }
                }
            }
        }

        // Fallback: try to get session_id from _ga_session cookie if exists
        if ($request->cookies->has('_ga_session')) {
            $sessionIdValue = $request->cookies->get('_ga_session');
            if (!empty($sessionIdValue)) {
                return (string) $sessionIdValue;
            }
        }

        // If no specific session cookie found, check for active PHP session
        if (PHP_SESSION_ACTIVE === session_status()) {
            $sessionId = session_id();
            if (false === $sessionId) {
                return null;
            }

            return '' !== $sessionId ? $sessionId : null;
        }

        return null;
    }
}
