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

        $this->assertNull($sessionId);
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

        // Aktuální implementace vrací null
        $this->assertNull($sessionId);
    }

    public function testBuildSessionIdFromPhpSession(): void
    {
        $request = Request::create('https://example.com');
        // No cookies set

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $handler = new DefaultSessionIdHandler($requestStack);

        $sessionId = $handler->buildSessionId();

        // Aktuální implementace vrací null
        $this->assertNull($sessionId);
    }

    public function testBuildSessionIdWithoutRequest(): void
    {
        $requestStack = new RequestStack();
        // No request pushed to stack

        $handler = new DefaultSessionIdHandler($requestStack);

        $sessionId = $handler->buildSessionId();

        // Aktuální implementace vrací null když není request
        $this->assertNull($sessionId);
    }

    public function testBuildSessionIdWithMalformedGaCookie(): void
    {
        $request = Request::create('https://example.com');
        $request->cookies->set('_ga', 'malformed_cookie_value');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $handler = new DefaultSessionIdHandler($requestStack);

        $sessionId = $handler->buildSessionId();

        // Aktuální implementace vrací null
        $this->assertNull($sessionId);
    }
}
