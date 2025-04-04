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
                // GA4 session cookie format is typically GS1.1.1743566249.16.0.1743566249.60.0.181542193
                // The session ID is the 3rd segment (1743566249 in this example)
                $gaCookie = (string) $gaCookie;
                $parts = explode('.', $gaCookie);
                if (count($parts) >= 3) {
                    return $parts[2];
                }
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
