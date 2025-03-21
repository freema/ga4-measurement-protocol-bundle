<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\GA4;

class ProductParameterBuilder
{
    /**
     * Build the parameter string for a product.
     *
     * @param array $product The product data
     *
     * @return string The formatted product parameter string
     */
    public function buildProductParameter(array $product): string
    {
        $productParam = '';

        // id parameter
        if (isset($product['sku'])) {
            $productParam .= 'id'.$product['sku'];
        }

        // name parameter
        if (isset($product['name'])) {
            $productParam .= '~nm'.$product['name'];
        }

        // brand parameter
        if (isset($product['brand'])) {
            $productParam .= '~br'.$product['brand'];
        }

        // quantity parameter
        if (isset($product['quantity'])) {
            $productParam .= '~qt'.$product['quantity'];
        }

        // price parameter
        if (isset($product['price'])) {
            $productParam .= '~pr'.number_format($product['price'], 2, '.', '');
        }

        // discount parameter
        if (isset($product['discount'])) {
            $productParam .= '~ds'.number_format($product['discount'], 2, '.', '');
        }

        // affiliation parameter
        if (isset($product['affiliation'])) {
            $productParam .= '~af'.$product['affiliation'];
        } elseif (isset($product['brand'])) {
            $productParam .= '~af'.$product['brand'].'.cz';
        }

        return $productParam;
    }
}
