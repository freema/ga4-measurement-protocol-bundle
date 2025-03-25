<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\Http;

use Freema\GA4MeasurementProtocolBundle\Exception\HttpClientConfigurationException;
use Freema\GA4MeasurementProtocolBundle\Http\DefaultHttpClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface as PsrClientInterface;

class DefaultHttpClientTest extends TestCase
{
    private Client|MockObject $client;
    private DefaultHttpClient $httpClient;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->httpClient = new DefaultHttpClient($this->client);
    }

    public function testPost(): void
    {
        $url = 'https://test-url.com';
        $payload = ['test' => 'data'];
        
        $this->client->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                $url,
                [
                    'json' => $payload,
                ]
            )
            ->willReturn(new Response(200, [], '{"status":"success"}'));
        
        $result = $this->httpClient->post($url, $payload);
        
        $this->assertEquals(200, $result['status']);
        $this->assertEquals('{"status":"success"}', $result['body']);
    }

    public function testPostWithOptions(): void
    {
        $url = 'https://test-url.com';
        $payload = ['test' => 'data'];
        $options = ['timeout' => 30];
        
        $this->client->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                $url,
                [
                    'json' => $payload,
                    'timeout' => 30,
                ]
            )
            ->willReturn(new Response(200, [], '{"status":"success"}'));
        
        $result = $this->httpClient->post($url, $payload, $options);
        
        $this->assertEquals(200, $result['status']);
        $this->assertEquals('{"status":"success"}', $result['body']);
    }

    public function testPostHandlesException(): void
    {
        $url = 'https://test-url.com';
        $payload = ['test' => 'data'];
        $request = new Request('POST', $url);
        $response = new Response(400, [], '{"error":"Bad Request"}');
        $exception = new ClientException('Bad Request', $request, $response);
        
        $this->client->expects($this->once())
            ->method('request')
            ->willThrowException($exception);
        
        $result = $this->httpClient->post($url, $payload);
        
        $this->assertEquals(400, $result['status']);
        $this->assertEquals('{"error":"Bad Request"}', $result['body']);
    }

    /**
     * @requires extension psr-http-client
     */
    public function testPostWithRealMockResponse(): void
    {
        if (!interface_exists(PsrClientInterface::class)) {
            $this->markTestSkipped('Extension psr-http-client is required.');
        }
        
        $psrClient = $this->createMock(PsrClientInterface::class);
        $httpClient = new DefaultHttpClient($psrClient);
        
        $url = 'https://test-url.com';
        $payload = ['test' => 'data'];
        
        // This should not throw an exception
        $result = $httpClient->post($url, $payload);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('body', $result);
    }
}