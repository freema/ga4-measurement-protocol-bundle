<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Request;

use Freema\GA4MeasurementProtocolBundle\Dto\Event\EventInterface;
use Freema\GA4MeasurementProtocolBundle\Dto\ExportableInterface;
use Freema\GA4MeasurementProtocolBundle\Exception\ValidationException;

/**
 * GA4 Measurement Protocol request.
 */
class RequestDto implements ExportableInterface
{
    private ?string $clientId = null;
    private ?string $userId = null;
    private ?string $apiSecret = null;
    private ?string $measurementId = null;
    private ?string $appInstanceId = null;
    private bool $nonPersonalizedAds = false;
    private ?int $timestampMicros = null;
    
    /**
     * @var EventInterface[]
     */
    private array $events = [];
    
    /**
     * Set the client ID.
     */
    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;
        return $this;
    }
    
    /**
     * Get the client ID.
     */
    public function getClientId(): ?string
    {
        return $this->clientId;
    }
    
    /**
     * Set the user ID.
     */
    public function setUserId(string $userId): self
    {
        $this->userId = $userId;
        return $this;
    }
    
    /**
     * Get the user ID.
     */
    public function getUserId(): ?string
    {
        return $this->userId;
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
     * Set the app instance ID.
     */
    public function setAppInstanceId(string $appInstanceId): self
    {
        $this->appInstanceId = $appInstanceId;
        return $this;
    }
    
    /**
     * Get the app instance ID.
     */
    public function getAppInstanceId(): ?string
    {
        return $this->appInstanceId;
    }
    
    /**
     * Set non-personalized ads flag.
     */
    public function setNonPersonalizedAds(bool $nonPersonalizedAds): self
    {
        $this->nonPersonalizedAds = $nonPersonalizedAds;
        return $this;
    }
    
    /**
     * Get non-personalized ads flag.
     */
    public function getNonPersonalizedAds(): bool
    {
        return $this->nonPersonalizedAds;
    }
    
    /**
     * Set timestamp in microseconds.
     */
    public function setTimestampMicros(int $timestampMicros): self
    {
        $this->timestampMicros = $timestampMicros;
        return $this;
    }
    
    /**
     * Get timestamp in microseconds.
     */
    public function getTimestampMicros(): ?int
    {
        return $this->timestampMicros;
    }
    
    /**
     * Add an event to the request.
     */
    public function addEvent(EventInterface $event): self
    {
        $this->events[] = $event;
        return $this;
    }
    
    /**
     * Get all events in the request.
     *
     * @return EventInterface[]
     */
    public function getEvents(): array
    {
        return $this->events;
    }
    
    /**
     * {@inheritdoc}
     */
    public function export(): array
    {
        $result = array_filter([
            'client_id' => $this->clientId,
            'user_id' => $this->userId,
            'app_instance_id' => $this->appInstanceId,
            'timestamp_micros' => $this->timestampMicros,
            'non_personalized_ads' => $this->nonPersonalizedAds ?: null,
        ]);
        
        // Add events
        $result['events'] = array_map(function (EventInterface $event) {
            return $event->export();
        }, $this->events);
        
        return $result;
    }
    
    /**
     * Validate the request.
     *
     * @param string|null $context Either 'web' or 'firebase'
     * @throws ValidationException
     */
    public function validate(?string $context = 'web'): bool
    {
        // Client ID is required for web
        if ($context === 'web' && empty($this->clientId)) {
            throw new ValidationException('Parameter "client_id" is required for web context');
        }
        
        // App instance ID is required for firebase
        if ($context === 'firebase' && empty($this->appInstanceId)) {
            throw new ValidationException('Parameter "app_instance_id" is required for firebase context');
        }
        
        // Cannot have both client ID and app instance ID
        if ($this->clientId && $this->appInstanceId) {
            throw new ValidationException('Cannot specify both "client_id" and "app_instance_id"');
        }
        
        // At least one event is required
        if (empty($this->events)) {
            throw new ValidationException('At least one event is required');
        }
        
        // Validate all events
        foreach ($this->events as $event) {
            $event->validate();
        }
        
        return true;
    }
}