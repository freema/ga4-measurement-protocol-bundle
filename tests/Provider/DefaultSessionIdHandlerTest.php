<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\Provider;

use Freema\GA4MeasurementProtocolBundle\Provider\DefaultSessionIdHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class DefaultSessionIdHandlerTest extends TestCase
{
    private RequestStack $requestStack;
    private DefaultSessionIdHandler $handler;

    protected function setUp(): void
    {
        $this->requestStack = new RequestStack();
        $this->handler = new DefaultSessionIdHandler($this->requestStack);
    }

    public function testBuildSessionIdWithoutRequest(): void
    {
        $sessionId = $this->handler->buildSessionId();
        $this->assertNull($sessionId);
    }

    public function testBuildSessionIdWithStandardGA4Format(): void
    {
        $request = Request::create('https://example.com');
        $request->cookies->set('_ga_ABC123', 'GS1.1.1757570945.16.0.1757570945.60.0.181542193');

        $this->requestStack->push($request);
        $this->handler->setTrackingId('G-ABC123');

        $sessionId = $this->handler->buildSessionId();

        $this->assertEquals('1757570945', $sessionId);
    }

    public function testBuildSessionIdWithTransformedGA4Format(): void
    {
        $request = Request::create('https://example.com');
        $request->cookies->set('_ga_XYZ789', 'GS2.1.s1757570945$o1$g0$t1757570949$j56$l0$h1734790078');

        $this->requestStack->push($request);
        $this->handler->setTrackingId('G-XYZ789');

        $sessionId = $this->handler->buildSessionId();

        $this->assertEquals('1757570945', $sessionId);
    }

    public function testBuildSessionIdWithTrackingIdWithoutGPrefix(): void
    {
        $request = Request::create('https://example.com');
        $request->cookies->set('_ga_ABC123', 'GS1.1.1757570945.16.0.1757570945.60.0.181542193');

        $this->requestStack->push($request);
        $this->handler->setTrackingId('ABC123');

        $sessionId = $this->handler->buildSessionId();

        $this->assertEquals('1757570945', $sessionId);
    }

    public function testBuildSessionIdWithMixedPrefixFormat(): void
    {
        $request = Request::create('https://example.com');
        $request->cookies->set('_ga_TEST123', 'GS1.1.s1757570945.16.0.1757570945.60.0.181542193');

        $this->requestStack->push($request);
        $this->handler->setTrackingId('G-TEST123');

        $sessionId = $this->handler->buildSessionId();

        $this->assertEquals('1757570945', $sessionId);
    }

    public function testBuildSessionIdFromGaSessionCookie(): void
    {
        $request = Request::create('https://example.com');
        $request->cookies->set('_ga_session', '9876543210');

        $this->requestStack->push($request);

        $sessionId = $this->handler->buildSessionId();

        $this->assertEquals('9876543210', $sessionId);
    }

    public function testBuildSessionIdFromGaSessionCookieWithSpecificGaCookie(): void
    {
        $request = Request::create('https://example.com');
        $request->cookies->set('_ga_ABC123', 'GS1.1.1757570945.16.0.1757570945.60.0.181542193');
        $request->cookies->set('_ga_session', '9876543210');

        $this->requestStack->push($request);
        $this->handler->setTrackingId('G-ABC123');

        $sessionId = $this->handler->buildSessionId();

        $this->assertEquals('1757570945', $sessionId);
    }

    public function testBuildSessionIdFromPhpSession(): void
    {
        $request = Request::create('https://example.com');

        $this->requestStack->push($request);

        @session_start();
        @session_regenerate_id();
        $expectedSessionId = session_id();

        $sessionId = $this->handler->buildSessionId();

        $this->assertEquals($expectedSessionId, $sessionId);

        @session_write_close();
    }

    public function testBuildSessionIdWithMalformedGaCookie(): void
    {
        $request = Request::create('https://example.com');
        $request->cookies->set('_ga_ABC123', 'malformed_cookie_value');

        $this->requestStack->push($request);
        $this->handler->setTrackingId('G-ABC123');

        $sessionId = $this->handler->buildSessionId();

        $this->assertNull($sessionId);
    }

    public function testBuildSessionIdWithEmptyGaCookie(): void
    {
        $request = Request::create('https://example.com');
        $request->cookies->set('_ga_ABC123', '');

        $this->requestStack->push($request);
        $this->handler->setTrackingId('G-ABC123');

        $sessionId = $this->handler->buildSessionId();

        $this->assertNull($sessionId);
    }

    public function testBuildSessionIdWithWrongTrackingId(): void
    {
        $request = Request::create('https://example.com');
        $request->cookies->set('_ga_ABC123', 'GS1.1.1757570945.16.0.1757570945.60.0.181542193');

        $this->requestStack->push($request);
        $this->handler->setTrackingId('G-DIFFERENT');

        $sessionId = $this->handler->buildSessionId();

        $this->assertNull($sessionId);
    }

    public function testBuildSessionIdWithEmptyGaSessionCookie(): void
    {
        $request = Request::create('https://example.com');
        $request->cookies->set('_ga_session', '');

        $this->requestStack->push($request);

        $sessionId = $this->handler->buildSessionId();

        $this->assertNull($sessionId);
    }

    public function testBuildSessionIdPriority(): void
    {
        $request = Request::create('https://example.com');
        $request->cookies->set('_ga_ABC123', 'GS1.1.1111111111.16.0.1111111111.60.0.181542193');
        $request->cookies->set('_ga_session', '2222222222');

        $this->requestStack->push($request);

        @session_start();
        @session_regenerate_id();

        $this->handler->setTrackingId('G-ABC123');

        $sessionId = $this->handler->buildSessionId();

        $this->assertEquals('1111111111', $sessionId);

        @session_write_close();
    }

    public function testBuildSessionIdWithVariousTransformedFormats(): void
    {
        $testCases = [
            'GS2.1.s1757570945$o1$g0$t1757570949$j56$l0$h1734790078' => '1757570945',
            'GS2.1.s9876543210$o5$g10$t1757570949$j100$l20$h1734790078' => '9876543210',
            'GS2.1.s123$o1$g0$t1757570949$j56$l0$h1734790078' => '123',
        ];

        foreach ($testCases as $cookieValue => $expectedSessionId) {
            $request = Request::create('https://example.com');
            $request->cookies->set('_ga_TEST', $cookieValue);

            $this->requestStack->push($request);
            $this->handler->setTrackingId('G-TEST');

            $sessionId = $this->handler->buildSessionId();

            $this->assertEquals($expectedSessionId, $sessionId, "Failed for cookie value: $cookieValue");

            $this->requestStack->pop();
        }
    }

    public function testBuildSessionIdWithVariousStandardFormats(): void
    {
        $testCases = [
            'GS1.1.1757570945.16.0.1757570945.60.0.181542193' => '1757570945',
            'GS1.1.9876543210.1.0.9876543210.1.0.1' => '9876543210',
            'GS1.1.123456.16.0.123456.60.0.181542193' => '123456',
        ];

        foreach ($testCases as $cookieValue => $expectedSessionId) {
            $request = Request::create('https://example.com');
            $request->cookies->set('_ga_TEST', $cookieValue);

            $this->requestStack->push($request);
            $this->handler->setTrackingId('G-TEST');

            $sessionId = $this->handler->buildSessionId();

            $this->assertEquals($expectedSessionId, $sessionId, "Failed for cookie value: $cookieValue");

            $this->requestStack->pop();
        }
    }
}
