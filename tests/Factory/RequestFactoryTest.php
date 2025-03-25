<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\Factory;

use Freema\GA4MeasurementProtocolBundle\Dto\Request\RequestDto;
use Freema\GA4MeasurementProtocolBundle\Factory\RequestFactory;
use PHPUnit\Framework\TestCase;

class RequestFactoryTest extends TestCase
{
    private RequestFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new RequestFactory();
    }

    public function testCreateRequest(): void
    {
        $request = $this->factory->createRequest();
        
        $this->assertInstanceOf(RequestDto::class, $request);
    }
}