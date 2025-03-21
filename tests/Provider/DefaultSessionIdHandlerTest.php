<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\Provider;

use Freema\GA4MeasurementProtocolBundle\Provider\DefaultSessionIdHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class DefaultSessionIdHandlerTest extends TestCase
{
    public function testBuildSessionIdFromSessionCookie(): void
    {
        $request = Request::create('https://example.com');
        $request->cookies->set('_ga_session_id', '1234567890');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $handler = new DefaultSessionIdHandler($requestStack);

        $sessionId = $handler->buildSessionId();

        $this->assertEquals('1234567890', $sessionId);
    }

    public function testBuildSessionIdFromGaCookie(): void
    {
        $request = Request::create('https://example.com');
        // No _ga_session_id cookie set
        $request->cookies->set('_ga', 'GA1.2.1234567890.9876543210');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $handler = new DefaultSessionIdHandler($requestStack);

        $sessionId = $handler->buildSessionId();

        // Should extract the session ID part from the _ga cookie
        $this->assertEquals('1234567890.9876543210', $sessionId);
    }

    public function testBuildSessionIdFromPhpSession(): void
    {
        $request = Request::create('https://example.com');
        // No cookies set

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $handler = new DefaultSessionIdHandler($requestStack);

        $sessionId = $handler->buildSessionId();

        // Should use session_id() as fallback
        $this->assertNotNull($sessionId);
    }

    public function testBuildSessionIdWithoutRequest(): void
    {
        $requestStack = new RequestStack();
        // No request pushed to stack

        $handler = new DefaultSessionIdHandler($requestStack);

        $sessionId = $handler->buildSessionId();

        // Should still return a session ID from PHP session
        $this->assertNotNull($sessionId);
    }

    public function testBuildSessionIdWithMalformedGaCookie(): void
    {
        $request = Request::create('https://example.com');
        $request->cookies->set('_ga', 'malformed_cookie_value');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $handler = new DefaultSessionIdHandler($requestStack);

        $sessionId = $handler->buildSessionId();

        // Should fallback to session_id() when _ga cookie format doesn't match
        $this->assertNotNull($sessionId);
    }
}
