<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\GA4;

use Freema\GA4MeasurementProtocolBundle\Dto\Request\RequestDto;
use Freema\GA4MeasurementProtocolBundle\Dto\Response\BaseResponse;
use Freema\GA4MeasurementProtocolBundle\Dto\Response\DebugResponse;
use Freema\GA4MeasurementProtocolBundle\Exception\MisconfigurationException;
use Freema\GA4MeasurementProtocolBundle\Http\HttpClientInterface;

/**
 * Service for sending GA4 Measurement Protocol requests.
 */
class Service
{
    private const COLLECT_ENDPOINT = 'google-analytics.com/mp/collect';
    private const COLLECT_DEBUG_ENDPOINT = 'google-analytics.com/debug/mp/collect';
    private const SSL_SCHEME = 'https://';
    
    private ?string $apiSecret = null;
    private ?string $measurementId = null;
    private ?string $firebaseId = null;
    private ?string $ipOverride = null;
    private ?array $options = null;
    
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        ?string $apiSecret = null,
        ?string $measurementId = null
    ) {
        $this->apiSecret = $apiSecret;
        
        if ($measurementId) {
            $this->measurementId = $measurementId;
        }
    }
    
    /**
     * Send a request to GA4.
     */
    public function send(RequestDto $request, bool $debug = false): BaseResponse
    {
        // Validate request
        $request->validate($this->measurementId ? 'web' : 'firebase');
        
        // Prepare the endpoint URL
        $url = $this->buildEndpointUrl($debug);
        
        // Get API parameters for the query string
        $apiParams = $this->getApiParameters();
        
        // Add API parameters to the URL
        $url .= '?' . http_build_query($apiParams);
        
        // Prepare the JSON payload (request data)
        $payload = $request->export();
        
        // Send the request as POST with JSON payload
        $httpResponse = $this->httpClient->post($url, $payload, $this->options ?? []);
        
        // Return appropriate response
        return $debug 
            ? new DebugResponse($httpResponse) 
            : new BaseResponse($httpResponse);
    }
    
    /**
     * Send a request in debug mode.
     */
    public function sendDebug(RequestDto $request): DebugResponse
    {
        return $this->send($request, true);
    }
    
    /**
     * Build the endpoint URL (without query parameters for POST requests).
     */
    public function buildEndpointUrl(bool $debug = false): string
    {
        $endpoint = $debug ? self::COLLECT_DEBUG_ENDPOINT : self::COLLECT_ENDPOINT;
        return self::SSL_SCHEME . $endpoint;
    }
    
    /**
     * Get the API parameters for the request.
     * 
     * @throws MisconfigurationException
     */
    public function getApiParameters(): array
    {
        $parameters = [];
        
        if ($this->apiSecret !== null) {
            $parameters['api_secret'] = $this->apiSecret;
        }
        
        if ($this->measurementId !== null) {
            $parameters['measurement_id'] = $this->measurementId;
        }
        
        if ($this->firebaseId !== null) {
            $parameters['firebase_app_id'] = $this->firebaseId;
        }
        
        if (!empty($parameters['firebase_app_id']) && !empty($parameters['measurement_id'])) {
            throw new MisconfigurationException("Cannot specify both 'measurement_id' and 'firebase_app_id'.");
        }
        
        // Add IP override if set
        if ($this->ipOverride) {
            $parameters['uip'] = $this->ipOverride;
            $parameters['_uip'] = $this->ipOverride; // For GA4 compatibility
        }
        
        return array_filter($parameters);
    }
    
    /**
     * Set the API secret.
     */
    public function setApiSecret(string $apiSecret): self
    {
        $this->apiSecret = $apiSecret;
        return $this;
    }
    
    /**
     * Get the API secret.
     */
    public function getApiSecret(): ?string
    {
        return $this->apiSecret;
    }
    
    /**
     * Set the measurement ID.
     */
    public function setMeasurementId(string $measurementId): self
    {
        $this->measurementId = $measurementId;
        return $this;
    }
    
    /**
     * Get the measurement ID.
     */
    public function getMeasurementId(): ?string
    {
        return $this->measurementId;
    }
    
    /**
     * Set the Firebase ID.
     */
    public function setFirebaseId(string $firebaseId): self
    {
        $this->firebaseId = $firebaseId;
        return $this;
    }
    
    /**
     * Get the Firebase ID.
     */
    public function getFirebaseId(): ?string
    {
        return $this->firebaseId;
    }
    
    /**
     * Set the IP override.
     */
    public function setIpOverride(string $ipOverride): self
    {
        $this->ipOverride = $ipOverride;
        return $this;
    }
    
    /**
     * Get the IP override.
     */
    public function getIpOverride(): ?string
    {
        return $this->ipOverride;
    }
    
    /**
     * Set the options.
     */
    public function setOptions(?array $options): self
    {
        $this->options = $options;
        return $this;
    }
    
    /**
     * Get the options.
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }
}