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

        if ($request->cookies->has('_ga')) {
            $gaCookie = $request->cookies->get('_ga');
            $gaCookie = (string) $gaCookie;
            $parts = explode('.', $gaCookie);

            // GA cookie format is typically GA1.2.XXXXXXXXXX.YYYYYYYYYY
            // We need the last two parts to form the client ID
            if (count($parts) >= 3) {
                return $parts[count($parts) - 2].'.'.$parts[count($parts) - 1];
            }
        }

        // If _ga cookie wasn't found or had invalid format, try session ID if available
        if (PHP_SESSION_ACTIVE === session_status()) {
            $sessionId = session_id();
            if (!empty($sessionId)) {
                // Make sure it's formatted properly for GA (numbers only)
                $cleanSessionId = preg_replace('/[^0-9]/', '', $sessionId);
                $cleanSessionId = (string) $cleanSessionId;
                if (strlen($cleanSessionId) > 0) {
                    return substr($cleanSessionId, 0, 10).'.'.time();
                }
            }
        }

        // If all else fails, generate a UUID-like string
        return sprintf(
            '%04x%04x.%04x%04x',
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF)
        );
    }
}
