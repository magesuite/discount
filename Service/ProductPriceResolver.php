<?php

namespace MageSuite\Discount\Service;

class ProductPriceResolver implements \MageSuite\Discount\Api\ProductPriceResolverInterface
{
    /**
     * @var \MageSuite\Discount\Model\ProductPriceResolverPool
     */
    protected $productPriceResolverPool;

    /**
     * @var \MageSuite\Discount\Api\Data\ProductPricesInterfaceFactory
     */
    protected $productPricesFactory;

    public function __construct(
        \MageSuite\Discount\Model\ProductPriceResolverPool $productPriceResolverPool,
        \MageSuite\Discount\Api\Data\ProductPricesInterfaceFactory $productPricesFactory
    ) {
        $this->productPriceResolverPool = $productPriceResolverPool;
        $this->productPricesFactory = $productPricesFactory;
    }
    public function getPrices($product, $finalPrice)
    {
        $productPriceResolver = $this->productPriceResolverPool->getProductPriceResolver($product->getTypeId());

        if (!$productPriceResolver) {
            return $this->productPricesFactory->create();
        }

        return $productPriceResolver->getPrices($product, $finalPrice);
    }
}
