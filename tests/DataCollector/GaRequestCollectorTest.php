<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\DataCollector;

use Freema\GA4MeasurementProtocolBundle\DataCollector\GaRequestCollector;
use Freema\GA4MeasurementProtocolBundle\Domain\AnalyticsRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GaRequestCollectorTest extends TestCase
{
    public function testCollectStoresTheAddedRequests(): void
    {
        $collector = new GaRequestCollector();
        $collector->addRequest(new AnalyticsRequest('https://www.google-analytics.com/mp/collect', ['client_id' => '123']));

        $collector->collect(new Request(), new Response());

        $this->assertSame(1, $collector->getCount());
        $this->assertSame('https://www.google-analytics.com/mp/collect', $collector->getData()[0]['uri']);
        $this->assertArrayHasKey('timestamp', $collector->getData()[0]['parameters']);
    }

    public function testResetClearsCollectedAndPendingRequests(): void
    {
        $collector = new GaRequestCollector();
        $collector->addRequest(new AnalyticsRequest('https://www.google-analytics.com/mp/collect'));
        $collector->collect(new Request(), new Response());

        $collector->reset();
        $collector->collect(new Request(), new Response());

        $this->assertSame(0, $collector->getCount());
        $this->assertSame([], $collector->getData());
    }
}
