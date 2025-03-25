<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Http;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class DefaultHttpClient implements HttpClientInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private Psr18Client $client;

    /**
     * @param array                $config Configuration options for Symfony HTTP client
     * @param LoggerInterface|null $logger Optional logger
     */
    public function __construct(
        array $config = [],
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();

        // Adjust proxy configuration format for Symfony HTTP client
        if (isset($config['proxy']) && is_array($config['proxy'])) {
            // Convert Guzzle proxy format to Symfony HTTP client format
            $proxyConfig = $this->convertProxyFormat($config['proxy']);
            $config['proxy'] = $proxyConfig;
        }

        // Create Symfony HTTP client with provided config
        $httpClient = HttpClient::create($config);

        // Wrap it with PSR-18 adapter for consistent interface
        $this->client = new Psr18Client($httpClient);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $url): ?ResponseInterface
    {
        try {
            if ($this->logger) {
                $this->logger->debug('Sending GET request', ['url' => $url]);
            }
            $response = $this->client->sendRequest(
                $this->client->createRequest('GET', $url)
            );

            $this->logger?->debug('Request successful', [
                'status_code' => $response->getStatusCode(),
                'url' => $url,
            ]);

            return $response;
        } catch (ExceptionInterface|ClientExceptionInterface $e) {
            $this->logger?->error('Failed to send GET request', [
                'exception' => $e,
                'message' => $e->getMessage(),
                'url' => $url,
            ]);

            return null;
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function post(string $url, array $data, array $options = []): ?ResponseInterface
    {
        try {
            if ($this->logger) {
                $this->logger->debug('Sending POST request', [
                    'url' => $url,
                    'data' => $data,
                    'options' => $options,
                ]);
            }
            
            // Create request body with JSON content
            $body = json_encode($data);
            if ($body === false) {
                throw new \InvalidArgumentException('Failed to JSON encode request data');
            }
            
            // Create PSR-7 request with JSON body
            $request = $this->client->createRequest('POST', $url)
                ->withHeader('Content-Type', 'application/json')
                ->withBody(\Nyholm\Psr7\Stream::create($body));
            
            // Add additional headers from options
            if (isset($options['headers']) && is_array($options['headers'])) {
                foreach ($options['headers'] as $name => $value) {
                    $request = $request->withHeader($name, $value);
                }
            }
            
            // Send the request
            $response = $this->client->sendRequest($request);

            $this->logger?->debug('POST request successful', [
                'status_code' => $response->getStatusCode(),
                'url' => $url,
            ]);

            return $response;
        } catch (ExceptionInterface|ClientExceptionInterface $e) {
            $this->logger?->error('Failed to send POST request', [
                'exception' => $e,
                'message' => $e->getMessage(),
                'url' => $url,
            ]);

            return null;
        }
    }

    /**
     * Convert Guzzle proxy format to Symfony HTTP client format.
     *
     * Guzzle format:
     * [
     *    'http' => 'proxy.example.com:3128',
     *    'https' => 'proxy.example.com:3128',
     *    'no' => ['localhost', '.example.com']
     * ]
     *
     * Symfony format:
     * 'http://proxy.example.com:3128' or ['http://user:pass@proxy.example.com:3128']
     *
     * @param array $proxyConfig Proxy configuration in Guzzle format
     *
     * @return string|null Proxy URL in Symfony format
     */
    private function convertProxyFormat(array $proxyConfig): ?string
    {
        // If http proxy is defined, use it
        if (isset($proxyConfig['http']) && is_string($proxyConfig['http'])) {
            // Make sure the URL has the protocol
            if (!str_starts_with($proxyConfig['http'], 'http://')) {
                return 'http://'.$proxyConfig['http'];
            }

            return $proxyConfig['http'];
        }

        // If https proxy is defined, use it
        if (isset($proxyConfig['https']) && is_string($proxyConfig['https'])) {
            // Make sure the URL has the protocol
            if (!str_starts_with($proxyConfig['https'], 'http')) {
                return 'http://'.$proxyConfig['https'];
            }

            return $proxyConfig['https'];
        }

        // No proxy defined
        return null;
    }
}
