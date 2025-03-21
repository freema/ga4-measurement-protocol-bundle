<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\Http;

use Freema\GA4MeasurementProtocolBundle\Http\DefaultHttpClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

class DefaultHttpClientTest extends TestCase
{
    public function testGetSendsRequest(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        // Instead of mocking Psr18Client which is final, we'll create a mock request and mock response
        $mockRequest = $this->createStub(\Psr\Http\Message\RequestInterface::class);
        $mockClient = $this->getMockBuilder(\Symfony\Contracts\HttpClient\HttpClientInterface::class)
            ->getMock();

        $logger = $this->createMock(LoggerInterface::class);

        // We'll test with a real MockHttpClient instead
        $mockResponse = new MockResponse('OK', ['http_code' => 200]);
        $mockHttpClient = new MockHttpClient($mockResponse);

        $client = new DefaultHttpClient([], $logger);

        // Use reflection to inject our mock HTTP client
        $reflectionProperty = new \ReflectionProperty(DefaultHttpClient::class, 'client');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($client, new Psr18Client($mockHttpClient));

        $result = $client->get('https://example.com');

        $this->assertNotNull($result);
    }

    /**
     * @requires extension psr-http-client
     */
    public function testGetWithRealMockResponse(): void
    {
        $mockClient = new MockHttpClient([
            new MockResponse('OK', ['http_code' => 200]),
        ]);

        $config = [];
        $logger = $this->createMock(LoggerInterface::class);

        $client = new class ($mockClient, $config, $logger) extends DefaultHttpClient {
            private $mockClient;

            public function __construct($mockClient, array $config, LoggerInterface $logger)
            {
                $this->mockClient = $mockClient;
                parent::__construct($config, $logger);
            }

            protected function createHttpClient(array $config): \Symfony\Contracts\HttpClient\HttpClientInterface
            {
                return $this->mockClient;
            }
        };

        $response = $client->get('https://example.com');

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testGetHandlesExceptions(): void
    {
        $exception = new TransportException('Connection error');

        // Create a mock HTTP client that throws an exception
        $mockHttpClient = new MockHttpClient(function () use ($exception) {
            throw $exception;
        });

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with('Failed to send GET request', $this->callback(function ($context) {
                return isset($context['exception'])
                    && false !== strpos($context['message'], 'Connection error')
                    && 'https://example.com' === $context['url'];
            }));

        $client = new DefaultHttpClient([], $logger);

        // Use reflection to inject our mock HTTP client
        $reflectionProperty = new \ReflectionProperty(DefaultHttpClient::class, 'client');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($client, new Psr18Client($mockHttpClient));

        $result = $client->get('https://example.com');

        $this->assertNull($result);
    }
}
