<?php

namespace MageSuite\Discount\Model\ProductPriceResolver;

class ProductPriceResolver
{
    /**
     * @var \MageSuite\Discount\Api\Data\ProductPricesInterfaceFactory
     */
    protected $productPricesFactory;

    public function __construct(\MageSuite\Discount\Api\Data\ProductPricesInterfaceFactory $productPricesFactory)
    {
        $this->productPricesFactory = $productPricesFactory;
    }

    /**
     * @return \MageSuite\Discount\Api\Data\ProductPricesInterface
     */
    protected function getProductPricesContainer()
    {
        return $this->productPricesFactory->create();
    }
}
