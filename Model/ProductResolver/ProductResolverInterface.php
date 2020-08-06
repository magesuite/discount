<?php

namespace MageSuite\Discount\Model\ProductResolver;

interface ProductResolverInterface
{
    /**
     * @param string $productTypeId
     * @return bool
     */
    public function isApplicable($productTypeId);

    /**
     * @param $product
     * @param float $finalPrice
     * @return array
     */
    public function getPrices($product, $finalPrice);
}
