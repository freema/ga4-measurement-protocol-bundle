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

        // Try to get user ID from session
        $session = $request->getSession();
        if (null !== $session && $session->has('user_id')) {
            $userId = $session->get('user_id');

            /* @phpstan-ignore-next-line */
            return is_string($userId) ? $userId : (string) $userId;
        }

        // If no user ID is available, return null
        return null;
    }
}
