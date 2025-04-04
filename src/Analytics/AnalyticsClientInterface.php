<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Analytics;

use Freema\GA4MeasurementProtocolBundle\Domain\AnalyticsUrl;
use Freema\GA4MeasurementProtocolBundle\Event\EventInterface;
use Freema\GA4MeasurementProtocolBundle\Exception\ValidationException;

/**
 * Interface for GA4 analytics clients.
 */
interface AnalyticsClientInterface
{
    /**
     * Set the client ID.
     */
    public function setClientId(string $clientId): self;

    /**
     * Set the user ID.
     */
    public function setUserId(string $userId): self;

    /**
     * Set the session ID.
     */
    public function setSessionId(string $sessionId): self;

    /**
     * Set debug mode.
     */
    public function setDebugMode(bool $debugMode): self;

    /**
     * Add an event to be sent.
     */
    public function addEvent(EventInterface $event): self;

    /**
     * Add an event to be sent, validating it first.
     *
     * @throws ValidationException If the event validation fails
     */
    public function addValidatedEvent(EventInterface $event): self;

    /**
     * Get the last sent parameters.
     */
    public function getLastSentParameters(): array;

    /**
     * Send all queued events to GA4.
     *
     * @param bool $validate Whether to validate events before sending
     *
     * @throws ValidationException If validation is enabled and an event fails validation
     */
    public function send(bool $validate = false): AnalyticsUrl;
}
