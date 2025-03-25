<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\Provider;

use Freema\GA4MeasurementProtocolBundle\Provider\DefaultClientIdHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class DefaultClientIdHandlerTest extends TestCase
{
    public function testBuildClientIdFromCookie(): void
    {
        $request = Request::create('https://example.com');
        $request->cookies->set('_ga', 'GA1.2.1234567890.1234567890');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $handler = new DefaultClientIdHandler($requestStack);

        $clientId = $handler->buildClientId();

        $this->assertEquals('555', $clientId);
    }

    public function testBuildClientIdFromSession(): void
    {
        $request = Request::create('https://example.com');
        // No _ga cookie set

        $requestStack = new RequestStack();
        $requestStack->push($request);

        // Cannot mock this easily since session_id() is a global function
        $handler = new DefaultClientIdHandler($requestStack);

        $clientId = $handler->buildClientId();

        // Should use session_id() as fallback
        $this->assertNotNull($clientId);
    }

    public function testBuildClientIdWithoutRequest(): void
    {
        $requestStack = new RequestStack();
        // No request pushed to stack

        $handler = new DefaultClientIdHandler($requestStack);

        $clientId = $handler->buildClientId();

        $this->assertNull($clientId);
    }

    public function testBuildClientIdWithEmptyCookie(): void
    {
        $request = Request::create('https://example.com');
        $request->cookies->set('_ga', '');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $handler = new DefaultClientIdHandler($requestStack);

        $clientId = $handler->buildClientId();

        // Should use anonymous ID as fallback when cookie is empty
        $this->assertEquals('555', $clientId);
    }
}
