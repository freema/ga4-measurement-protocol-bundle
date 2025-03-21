<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\GA4;

use Symfony\Component\HttpFoundation\RequestStack;

class ParameterBuilder
{
    private const DEFAULT_GA4_ENDPOINT = 'https://region1.analytics.google.com/g/collect';

    private string $globalEndpoint;

    public function __construct(
        private readonly ProductParameterBuilder $productBuilder,
        private readonly RequestStack $requestStack,
        ?string $globalEndpoint = null,
    ) {
        $this->globalEndpoint = $globalEndpoint ?? self::DEFAULT_GA4_ENDPOINT;
    }

    /**
     * Build parameters for a GA4 request.
     *
     * @param string|null $customEndpoint Override the global endpoint for this request
     *
     * @return array The parameters and full request URL
     */
    public function buildParameters(AnalyticsGA4Data $data, ?string $customEndpoint = null): array
    {
        $queryParams = [
            'v' => $data->getProtocolVersion(),
            'tid' => $data->getTrackingId(),
            'gcs' => 'G111', // Default consent mode
        ];

        if ($data->getClientId()) {
            $queryParams['cid'] = $data->getClientId();
        }

        if ($data->getUserId()) {
            $queryParams['uid'] = $data->getUserId();
        }

        if ($data->getSessionId()) {
            $queryParams['sid'] = $data->getSessionId();
        }

        if ($data->getDocumentReferrer()) {
            $queryParams['dr'] = $data->getDocumentReferrer();
        }

        if ($data->getDocumentTitle()) {
            $queryParams['dt'] = $data->getDocumentTitle();
        }

        $queryParams['cu'] = $data->getCurrency();

        // Get the current request URL
        $request = $this->requestStack->getCurrentRequest();
        if ($data->getDocumentPath()) {
            $host = $request?->getSchemeAndHttpHost() ?? '';
            $queryParams['dl'] = $host . $data->getDocumentPath();
        }

        // Add event name
        $queryParams['en'] = $data->getEventName();

        // Add products
        foreach ($data->getProducts() as $index => $product) {
            $prodIndex = $index + 1;
            $queryParams['pr'.$prodIndex] = $this->productBuilder->buildProductParameter($product);
        }

        // Add platform
        $queryParams['ep.platform'] = 'mp';

        // Add page_type
        $queryParams['ep.page_type'] = $data->getEventName();

        // Add transaction details for purchase events
        if ('purchase' === $data->getEventName()) {
            $this->addPurchaseParameters($queryParams, $data);
        }

        // Add custom parameters
        foreach ($data->getCustomParameters() as $key => $value) {
            $queryParams[$key] = $value;
        }

        // Determine which endpoint to use
        $endpoint = $customEndpoint ?? $this->globalEndpoint;

        // Build the request URL
        $url = $endpoint.'?'.http_build_query($queryParams);

        return [
            'params' => $queryParams,
            'url' => $url,
        ];
    }

    /**
     * Add purchase-specific parameters.
     */
    private function addPurchaseParameters(array &$queryParams, AnalyticsGA4Data $data): void
    {
        $transactionId = $data->getTransactionId();
        if ($transactionId) {
            $queryParams['ep.transaction_id'] = $transactionId;
        }

        $affiliation = $data->getAffiliation();
        if ($affiliation) {
            $queryParams['ep.affiliation'] = $affiliation;
        }

        $revenue = $data->getRevenue();
        if (null !== $revenue) {
            $formattedRevenue = number_format($revenue, 2, '.', '');
            $queryParams['epn.value'] = $formattedRevenue;
            $queryParams['epn.payment'] = $formattedRevenue;
        }

        $tax = $data->getTax();
        if (null !== $tax) {
            $queryParams['epn.tax'] = number_format($tax, 2, '.', '');
        }

        $queryParams['epn.shipping'] = number_format($data->getShipping(), 2, '.', '');
        $queryParams['epn.discount'] = number_format($data->getDiscount(), 2, '.', '');

        $paymentType = $data->getPaymentType();
        if ($paymentType) {
            $queryParams['ep.payment_type'] = $paymentType;
        }

        $shippingTier = $data->getShippingTier();
        if ($shippingTier) {
            $queryParams['ep.shipping_tier'] = $shippingTier;
        }
    }
}
