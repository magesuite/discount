<?php

namespace MageSuite\Discount\Model;

class ProductResolverPool
{
    protected $productResolvers;

    public function __construct(array $productResolvers)
    {
        $this->productResolvers = $productResolvers;
    }

    public function getProductResolver($productTypeId)
    {
        foreach ($this->productResolvers as $productResolver) {
            if (!$productResolver->isApplicable($productTypeId)) {
                continue;
            }

            return $productResolver;
        }

        return null;
    }
}
