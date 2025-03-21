<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\GA4;

class AnalyticsGA4Data
{
    private string $protocolVersion = '2';
    private string $trackingId;
    private ?string $clientId = null;
    private ?string $userId = null;
    private ?string $userAgent = null;
    private ?string $documentPath = null;
    private ?string $documentReferrer = null;
    private ?string $documentTitle = null;
    private ?string $sessionId = null;
    private string $currency = 'CZK';
    private ?string $transactionId = null;
    private ?float $revenue = null;
    private ?float $tax = null;
    private float $shipping = 0.0;
    private float $discount = 0.0;
    private string $affiliation = 'WebStore';
    private ?string $paymentType = null;
    private ?string $shippingTier = null;
    private array $products = [];
    private array $customParameters = [];
    private ?string $eventName = null;

    // Add getters and setters for all properties
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function setProtocolVersion(string $protocolVersion): self
    {
        $this->protocolVersion = $protocolVersion;

        return $this;
    }

    public function getTrackingId(): string
    {
        return $this->trackingId;
    }

    public function setTrackingId(string $trackingId): self
    {
        $this->trackingId = $trackingId;

        return $this;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getDocumentPath(): ?string
    {
        return $this->documentPath;
    }

    public function setDocumentPath(string $path): self
    {
        $this->documentPath = $path;

        return $this;
    }

    public function getDocumentReferrer(): ?string
    {
        return $this->documentReferrer;
    }

    public function setDocumentReferrer(string $referrer): self
    {
        $this->documentReferrer = $referrer;

        return $this;
    }

    public function getDocumentTitle(): ?string
    {
        return $this->documentTitle;
    }

    public function setDocumentTitle(string $title): self
    {
        $this->documentTitle = $title;

        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): self
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(string $transactionId): self
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    public function getRevenue(): ?float
    {
        return $this->revenue;
    }

    public function setRevenue(float $revenue): self
    {
        $this->revenue = $revenue;

        return $this;
    }

    public function getTax(): ?float
    {
        return $this->tax;
    }

    public function setTax(float $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function getShipping(): float
    {
        return $this->shipping;
    }

    public function setShipping(float $shipping): self
    {
        $this->shipping = $shipping;

        return $this;
    }

    public function getDiscount(): float
    {
        return $this->discount;
    }

    public function setDiscount(float $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    public function getAffiliation(): string
    {
        return $this->affiliation;
    }

    public function setAffiliation(string $affiliation): self
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    public function getPaymentType(): ?string
    {
        return $this->paymentType;
    }

    public function setPaymentType(string $paymentType): self
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    public function getShippingTier(): ?string
    {
        return $this->shippingTier;
    }

    public function setShippingTier(string $shippingTier): self
    {
        $this->shippingTier = $shippingTier;

        return $this;
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    public function addProduct(array $product): self
    {
        $this->products[] = $product;

        return $this;
    }

    public function getCustomParameters(): array
    {
        return $this->customParameters;
    }

    public function addCustomParameter(string $key, string $value): self
    {
        $this->customParameters[$key] = $value;

        return $this;
    }

    public function getEventName(): ?string
    {
        return $this->eventName ?? 'custom_event';
    }

    public function setEventName(string $name): self
    {
        $this->eventName = $name;

        return $this;
    }

    public function setEventCategory(string $category): self
    {
        $this->customParameters['ep.category'] = $category;

        return $this;
    }

    public function setEventAction(string $action): self
    {
        $this->customParameters['ep.action'] = $action;

        return $this;
    }

    public function setProductActionToPurchase(): self
    {
        $this->eventName = 'purchase';

        return $this;
    }
}
