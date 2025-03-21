<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Http;

use Psr\Log\LoggerInterface;

class DefaultHttpClientFactory implements HttpClientFactoryInterface
{
    /**
     * @param LoggerInterface|null $logger Optional logger
     */
    public function __construct(
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function createHttpClient(array $config = []): HttpClientInterface
    {
        return new DefaultHttpClient($config, $this->logger);
    }
}
