<?php

namespace MageSuite\Discount\Model\ProductPriceResolver;

class ProductPriceResolver
{
    /**
     * @var \MageSuite\Discount\Api\Data\ProductPricesInterfaceFactory
     */
    protected $productPricesFactory;

    /**
     * @var \MageSuite\Discount\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \MageSuite\Discount\Api\Data\ProductPricesInterfaceFactory $productPricesFactory,
        \MageSuite\Discount\Helper\Configuration $configuration
    ) {
        $this->productPricesFactory = $productPricesFactory;
        $this->configuration = $configuration;
    }

    /**
     * @return \MageSuite\Discount\Api\Data\ProductPricesInterface
     */
    protected function getProductPricesContainer()
    {
        return $this->productPricesFactory->create();
    }
}
