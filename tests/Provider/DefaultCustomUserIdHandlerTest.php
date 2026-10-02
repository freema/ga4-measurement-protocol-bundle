<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\Provider;

use Freema\GA4MeasurementProtocolBundle\Provider\DefaultCustomUserIdHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class DefaultCustomUserIdHandlerTest extends TestCase
{
    public function testWithoutRequest(): void
    {
        $this->assertNull((new DefaultCustomUserIdHandler(new RequestStack()))->buildUserId());
    }

    public function testRequestWithoutSession(): void
    {
        // Stateless routes and API calls have no session; getSession() would throw
        $this->assertNull($this->handlerFor(new Request())->buildUserId());
    }

    public function testUserIdFromSession(): void
    {
        $this->assertSame('42', $this->handlerFor($this->requestWithSession(['user_id' => 42]))->buildUserId());
        $this->assertSame('abc', $this->handlerFor($this->requestWithSession(['user_id' => 'abc']))->buildUserId());
    }

    public function testSessionWithoutUserId(): void
    {
        $this->assertNull($this->handlerFor($this->requestWithSession([]))->buildUserId());
    }

    public function testUserIdThatIsNotAString(): void
    {
        $this->assertNull($this->handlerFor($this->requestWithSession(['user_id' => ['id' => 42]]))->buildUserId());
    }

    private function handlerFor(Request $request): DefaultCustomUserIdHandler
    {
        $requestStack = new RequestStack();
        $requestStack->push($request);

        return new DefaultCustomUserIdHandler($requestStack);
    }

    /**
     * @param array<string, mixed> $values
     */
    private function requestWithSession(array $values): Request
    {
        $session = new Session(new MockArraySessionStorage());
        foreach ($values as $name => $value) {
            $session->set($name, $value);
        }
        $request = new Request();
        $request->setSession($session);

        return $request;
    }
}
