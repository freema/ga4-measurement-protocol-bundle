<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Request;

use Freema\GA4MeasurementProtocolBundle\Dto\Common\EventCollection;
use Freema\GA4MeasurementProtocolBundle\Dto\Common\ConsentProperty;
use Freema\GA4MeasurementProtocolBundle\Dto\Event\AbstractEvent;
use Freema\GA4MeasurementProtocolBundle\Enum\ErrorCode;
use Freema\GA4MeasurementProtocolBundle\Exception\ValidationException;

class BaseRequest extends AbstractRequest
{
    /**
     * Unique identifier of user instance.
     * Required
     * @var string
     */
    protected ?string $clientId = null;

    /**
     * App Instance ID.
     * @var string
     */
    protected ?string $appInstanceId = null;

    /**
     * Unique identifier for a user.
     * Not required
     * @var string
     */
    protected ?string $userId = null;

    /**
     * An unix timestamp (microseconds) for the time to associate with the event.
     * Not requied
     * @var int
     */
    protected ?int $timestampMicros = null;

    /**
     * If set true - indicates that events should not be use for personalized ads.
     * Not required
     * @var ?bool
     */
    protected ?bool $nonPersonalizedAds = null;

    /**
     * Sets the consent settings for the request.
     * Replaces non_personalized_ads
     * Not required
     * @var ConsentProperty
     */
    protected ?ConsentProperty $consent = null;

    /**
     * API secret for validation
     * @var string|null
     */
    protected ?string $apiSecret = null;

    /**
     * Measurement ID (GA4)
     * @var string|null
     */
    protected ?string $measurementId = null;

    /**
     * Collection of event items. Maximum 25 events.
     * Required
     * @var EventCollection
     */
    protected EventCollection $events;

    /**
     * BaseRequest constructor.
     * @param string|null $clientId
     * @param AbstractEvent|EventCollection|null $events - Single Event or EventsCollection
     */
    public function __construct(?string $clientId = null, $events = null)
    {
        if ($clientId !== null) {
            $this->clientId = $clientId;
        }
        if ($events !== null) {
            if ($events instanceof EventCollection) {
                $this->events = $events;
            } else if ($events instanceof AbstractEvent) {
                $this->events = new EventCollection();
                $this->events->addEvent($events);
            }
        } else {
            $this->events = new EventCollection();
        }
    }

    /**
     * @param ConsentProperty|null $consent
     * @return BaseRequest
     */
    public function setConsent(?ConsentProperty $consent): self
    {
        $this->consent = $consent;
        return $this;
    }

    /**
     * @return ConsentProperty|null
     */
    public function getConsent(): ?ConsentProperty
    {
        return $this->consent;
    }

    /**
     * @param AbstractEvent $event
     * @return BaseRequest
     */
    public function addEvent(AbstractEvent $event): self
    {
        $this->getEvents()->addEvent($event);
        return $this;
    }

    /**
     * @return EventCollection
     */
    public function getEvents(): EventCollection
    {
        return $this->events;
    }

    /**
     * @param EventCollection $events
     * @return BaseRequest
     */
    public function setEvents(EventCollection $events): self
    {
        $this->events = $events;
        return $this;
    }

    /**
     * @return array
     */
    public function export(): array
    {
        $exportBaseRequest = array_filter([
            'client_id' => $this->getClientId(),
            'app_instance_id' => $this->getAppInstanceId(),
            'events' => $this->getEvents()->export(),
        ]);

        if ($this->getNonPersonalizedAds() !== null) {
            $exportBaseRequest['non_personalized_ads'] = $this->isNonPersonalizedAds();
        }

        if ($this->getUserId() !== null) {
            $exportBaseRequest['user_id'] = $this->getUserId();
        }

        if ($this->getTimestampMicros() !== null) {
            $exportBaseRequest['timestamp_micros'] = $this->getTimestampMicros();
        }

        if ($this->getConsent() !== null) {
            $exportBaseRequest['consent'] = $this->getConsent()->export();
        }

        return $exportBaseRequest;
    }

    /**
     * @return string
     */
    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     * @return BaseRequest
     */
    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAppInstanceId(): ?string
    {
        return $this->appInstanceId;
    }

    /**
     * @param string $appInstanceId
     * @return BaseRequest
     */
    public function setAppInstanceId(string $appInstanceId): self
    {
        $this->appInstanceId = $appInstanceId;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNonPersonalizedAds(): bool
    {
        $nonPersonalizedAds = $this->getNonPersonalizedAds();
        if (!isset($nonPersonalizedAds)) {
            return false;
        }

        return $this->nonPersonalizedAds;
    }

    /**
     * @return ?bool
     */
    public function getNonPersonalizedAds(): ?bool
    {
        return $this->nonPersonalizedAds;
    }

    /**
     * @param bool $nonPersonalizedAds
     * @return BaseRequest
     */
    public function setNonPersonalizedAds(bool $nonPersonalizedAds): self
    {
        $this->nonPersonalizedAds = $nonPersonalizedAds;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUserId(): ?string
    {
        return $this->userId;
    }

    /**
     * @param string|null $userId
     * @return BaseRequest
     */
    public function setUserId(?string $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return ?int
     */
    public function getTimestampMicros(): ?int
    {
        return $this->timestampMicros;
    }

    /**
     * @param ?int $timestampMicros
     * @return BaseRequest
     */
    public function setTimestampMicros(?int $timestampMicros): self
    {
        $this->timestampMicros = $timestampMicros;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getApiSecret(): ?string
    {
        return $this->apiSecret;
    }

    /**
     * @param string|null $apiSecret
     * @return BaseRequest
     */
    public function setApiSecret(?string $apiSecret): self
    {
        $this->apiSecret = $apiSecret;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMeasurementId(): ?string
    {
        return $this->measurementId;
    }

    /**
     * @param string|null $measurementId
     * @return BaseRequest
     */
    public function setMeasurementId(?string $measurementId): self
    {
        $this->measurementId = $measurementId;
        return $this;
    }

    /**
     * @param string|null $context Context for request, either 'web' or 'firebase'.
     * @return bool
     * @throws ValidationException
     */
    public function validate(?string $context = 'web'): bool
    {
        if ($context === 'web' && empty($this->getClientId())) {
            throw new ValidationException('Parameter "client_id" is required.', ErrorCode::VALIDATION_CLIENT_ID_REQUIRED, 'client_id');
        }
        
        if ($context === 'firebase' && empty($this->getAppInstanceId())) {
            throw new ValidationException('Parameter "app_instance_id" is required.', ErrorCode::VALIDATION_APP_INSTANCE_ID_REQUIRED, 'app_instance_id');
        }
        
        if ($this->getClientId() && $this->getAppInstanceId()) {
            throw new ValidationException('Cannot specify both "client_id" and "app_instance_id".', ErrorCode::VALIDATION_CLIENT_IDENTIFIER_MISCONFIGURED);
        }

        // Validate events
        $this->getEvents()->validate();

        return true;
    }
}
