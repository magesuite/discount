<?php

namespace MageSuite\Discount\Api;

interface ProductPriceResolverInterface
{
    /**
     * @var $product
     * @var $finalPrice
     * @return array
     */
    public function getPrices($product, $finalPrice);
}
