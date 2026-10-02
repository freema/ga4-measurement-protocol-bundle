<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Provider;

use Symfony\Component\HttpFoundation\RequestStack;

class DefaultCustomUserIdHandler implements CustomUserIdHandler
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function buildUserId(): ?string
    {
        $request = $this->requestStack->getMainRequest();
        if (null === $request) {
            return null;
        }

        // getSession() throws on a request without a session (stateless
        // routes, API calls), which would make send() fail
        if (!$request->hasSession()) {
            return null;
        }

        $session = $request->getSession();
        if ($session->has('user_id')) {
            $userId = $session->get('user_id');

            return is_scalar($userId) || $userId instanceof \Stringable ? (string) $userId : null;
        }

        // If no user ID is available, return null
        return null;
    }
}
