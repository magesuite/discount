<?php

namespace MageSuite\Discount\Service;

class ProductPriceResolver implements \MageSuite\Discount\Api\ProductPriceResolverInterface
{
    /**
     * @var \MageSuite\Discount\Model\ProductResolverPool
     */
    protected $productResolverPool;

    public function __construct(\MageSuite\Discount\Model\ProductResolverPool $productResolverPool)
    {
        $this->productResolverPool = $productResolverPool;
    }
    public function getPrices($product, $finalPrice)
    {
        $productResolver = $this->productResolverPool->getProductResolver($product->getTypeId());

        if (!$productResolver) {
            return [
                'regular_price' => 0,
                'final_price' => 0
            ];
        }

        return $productResolver->getPrices($product, $finalPrice);
    }
}
