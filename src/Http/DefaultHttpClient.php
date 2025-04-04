<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Http;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\ResponseInterface;

class DefaultHttpClient implements HttpClientInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private array $httpOptions = [];

    public function __construct(
        array $config = [],
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();

        // Convert legacy proxy format to Symfony format if needed
        if (isset($config['proxy']) && is_array($config['proxy'])) {
            $this->convertProxyConfig($config['proxy']);
        }

        // Store any other HTTP options
        if (isset($config['http_options']) && is_array($config['http_options'])) {
            $this->httpOptions = array_merge($this->httpOptions, $config['http_options']);
        }

        // Apply other standard options
        if (isset($config['timeout'])) {
            $this->httpOptions['timeout'] = $config['timeout'];
        }

        if (isset($config['max_redirects'])) {
            $this->httpOptions['max_redirects'] = $config['max_redirects'];
        }
    }

    /**
     * Convert from legacy proxy array format to Symfony HttpClient format.
     */
    private function convertProxyConfig(array $proxyConfig): void
    {
        // Symfony format is a single string URL for proxy
        if (isset($proxyConfig['http'])) {
            $this->httpOptions['proxy'] = $proxyConfig['http'];
        } elseif (isset($proxyConfig['https'])) {
            $this->httpOptions['proxy'] = $proxyConfig['https'];
        }

        // Convert no_proxy array to comma-separated string
        if (isset($proxyConfig['no']) && is_array($proxyConfig['no'])) {
            $this->httpOptions['no_proxy'] = implode(',', $proxyConfig['no']);
        }
    }

    public function sendGA4Request(string $measurementId, string $apiSecret, array $payload, bool $debug = false): ResponseInterface
    {
        try {
            $this->logger?->debug('Sending GA4 request', [
                'measurement_id' => $measurementId,
                'debug' => $debug,
            ]);

            // Create HTTP client
            $client = HttpClient::create($this->httpOptions);

            // Build the URL
            $url = sprintf(
                'https://www.google-analytics.com/%smp/collect?measurement_id=%s&api_secret=%s',
                $debug ? 'debug/' : '',
                $measurementId,
                $apiSecret
            );

            // Send the request
            $response = $client->request('POST', $url, [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);

            $this->logger?->debug('GA4 response', [
                'status_code' => $response->getStatusCode(),
            ]);

            return $response;
        } catch (\Throwable $e) {
            $this->logger?->error('Failed to send GA4 request', [
                'exception' => $e,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Set HTTP client options.
     */
    public function setHttpOptions(array $options): self
    {
        $this->httpOptions = $options;

        return $this;
    }

    /**
     * Add HTTP client options.
     */
    public function addHttpOptions(array $options): self
    {
        $this->httpOptions = array_merge($this->httpOptions, $options);

        return $this;
    }

    /**
     * Set proxy URL in Symfony format.
     */
    public function setProxy(string $proxyUrl): self
    {
        $this->httpOptions['proxy'] = $proxyUrl;

        return $this;
    }

    /**
     * Set domains to exclude from proxy.
     */
    public function setNoProxy(array|string $domains): self
    {
        if (is_array($domains)) {
            $this->httpOptions['no_proxy'] = implode(',', $domains);
        } else {
            $this->httpOptions['no_proxy'] = $domains;
        }

        return $this;
    }
}
