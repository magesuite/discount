<?php

namespace MageSuite\Discount\Model;

class ProductPriceResolverPool
{
    protected $productPriceResolvers;

    public function __construct(array $productPriceResolvers)
    {
        $this->productPriceResolvers = $productPriceResolvers;
    }

    public function getProductPriceResolver($productTypeId)
    {
        foreach ($this->productPriceResolvers as $productPriceResolver) {
            if (!$productPriceResolver->isApplicable($productTypeId)) {
                continue;
            }

            return $productPriceResolver;
        }

        return null;
    }
}
