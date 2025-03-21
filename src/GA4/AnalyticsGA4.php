<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\GA4;

use Freema\GA4MeasurementProtocolBundle\DataCollector\GaRequest;
use Freema\GA4MeasurementProtocolBundle\Http\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AnalyticsGA4
{
    private AnalyticsGA4Data $data;
    private LoggerInterface $logger;
    private ?string $customEndpoint = null;

    public function __construct(
        private readonly ParameterBuilder $parameterBuilder,
        private readonly HttpClientInterface $httpClient,
        private readonly EventDispatcherInterface $eventDispatcher,
        ?LoggerInterface $logger = null,
    ) {
        $this->data = new AnalyticsGA4Data();
        $this->logger = $logger ?? new NullLogger();
    }

    public function setProtocolVersion(string $version): self
    {
        $this->data->setProtocolVersion($version);

        return $this;
    }

    public function setTrackingId(string $trackingId): self
    {
        $this->data->setTrackingId($trackingId);

        return $this;
    }

    public function setClientId(string $clientId): self
    {
        $this->data->setClientId($clientId);

        return $this;
    }

    public function setUserId(string $userId): self
    {
        $this->data->setUserId($userId);

        return $this;
    }

    public function setUserAgentOverride(string $userAgent): self
    {
        $this->data->setUserAgent($userAgent);

        return $this;
    }

    public function setDocumentPath(string $path): self
    {
        $this->data->setDocumentPath($path);

        return $this;
    }

    public function setDocumentReferrer(string $referrer): self
    {
        $this->data->setDocumentReferrer($referrer);

        return $this;
    }

    public function setDocumentTitle(string $title): self
    {
        $this->data->setDocumentTitle($title);

        return $this;
    }

    public function setSessionId(string $sessionId): self
    {
        $this->data->setSessionId($sessionId);

        return $this;
    }

    public function setCurrency(string $currency): self
    {
        $this->data->setCurrency($currency);

        return $this;
    }

    public function setTransactionId(string $transactionId): self
    {
        $this->data->setTransactionId($transactionId);

        return $this;
    }

    public function setRevenue(float $revenue): self
    {
        $this->data->setRevenue($revenue);

        return $this;
    }

    public function setTax(float $tax): self
    {
        $this->data->setTax($tax);

        return $this;
    }

    public function setShipping(float $shipping): self
    {
        $this->data->setShipping($shipping);

        return $this;
    }

    public function setDiscount(float $discount): self
    {
        $this->data->setDiscount($discount);

        return $this;
    }

    public function setAffiliation(string $affiliation): self
    {
        $this->data->setAffiliation($affiliation);

        return $this;
    }

    public function setPaymentType(string $paymentType): self
    {
        $this->data->setPaymentType($paymentType);

        return $this;
    }

    public function setShippingTier(string $shippingTier): self
    {
        $this->data->setShippingTier($shippingTier);

        return $this;
    }

    public function addProduct(array $product): self
    {
        $this->data->addProduct($product);

        return $this;
    }

    public function setProductActionToPurchase(): self
    {
        $this->data->setProductActionToPurchase();

        return $this;
    }

    public function setEventCategory(string $category): self
    {
        $this->data->setEventCategory($category);

        return $this;
    }

    public function setEventAction(string $action): self
    {
        $this->data->setEventAction($action);

        return $this;
    }

    public function setEventName(string $name): self
    {
        $this->data->setEventName($name);

        return $this;
    }

    public function addCustomParameter(string $key, string $value): self
    {
        $this->data->addCustomParameter($key, $value);

        return $this;
    }

    public function setCustomEndpoint(string $endpoint): self
    {
        $this->customEndpoint = $endpoint;

        return $this;
    }

    public function sendPageview(): string
    {
        $this->data->setEventName('page_view');

        return $this->send();
    }

    public function sendEvent(): string
    {
        if (!$this->data->getEventName()) {
            $this->data->setEventName('custom_event');
        }

        return $this->send();
    }

    private function send(): string
    {
        try {
            // Build the parameters and URL
            $result = $this->parameterBuilder->buildParameters($this->data, $this->customEndpoint);
            $url = $result['url'];

            // Dispatch event for data collector before sending the request
            $this->eventDispatcher->dispatch(new GaRequest($url));

            // Log the request
            $this->logger->debug('Sending GA4 request', [
                'url' => $url,
                'parameters' => $result['params'],
            ]);

            // Send the request
            $this->httpClient->get($url);

            return $url;
        } catch (\Throwable $e) {
            // Log the error
            $this->logger->error('Error sending GA4 request', [
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            // Return the URL that would have been called
            return $url ?? '';
        }
    }
}
