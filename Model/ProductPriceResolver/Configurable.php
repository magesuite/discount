<?php

namespace MageSuite\Discount\Model\ProductPriceResolver;

class Configurable extends ProductPriceResolver implements ProductPriceResolverInterface
{
    /**
     * @var \MageSuite\Discount\Model\Command\GetMaxPriceForConfigurableProduct
     */
    protected $getMaxPriceForConfigurableProduct;

    /**
     * @var \MageSuite\Discount\Model\Command\GetMinSpecialPriceForConfigurableProduct
     */
    protected $getMinSpecialPriceForConfigurableProduct;

    /**
     * @var \MageSuite\Discount\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \MageSuite\Discount\Api\Data\ProductPricesInterfaceFactory $productPricesFactory,
        \MageSuite\Discount\Model\Command\GetMaxPriceForConfigurableProduct $getMaxPriceForConfigurableProduct,
        \MageSuite\Discount\Model\Command\GetMinSpecialPriceForConfigurableProduct $getMinSpecialPriceForConfigurableProduct,
        \MageSuite\Discount\Helper\Configuration $configuration
    ) {

        parent::__construct($productPricesFactory, $configuration);

        $this->getMaxPriceForConfigurableProduct = $getMaxPriceForConfigurableProduct;
        $this->getMinSpecialPriceForConfigurableProduct = $getMinSpecialPriceForConfigurableProduct;
        $this->configuration = $configuration;
    }

    public function isApplicable($productTypeId)
    {
        return $productTypeId == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;
    }

    public function getPrices($product, $finalPrice)
    {
        $regularPrice = $this->getMaxPriceForConfigurableProduct->execute($product);
        $productPricesContainer = $this->getProductPricesContainer();

        $productPricesContainer
            ->setRegularPrice($regularPrice);

        if ($finalPrice) {
            $productPricesContainer->setFinalPrice($finalPrice);
            return $productPricesContainer;
        }

        if ($this->configuration->isSpecialPriceResolverEnabled()) {
            $specialPriceMinimum = $this->getMinSpecialPriceForConfigurableProduct->execute($product, $regularPrice);
            $productPricesContainer->setFinalPrice($specialPriceMinimum);
            return $productPricesContainer;
        }

        $finalPrice = $product->hasData('min_price') ?
            $product->getData('min_price') :
            $product->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)->getAmount()->getValue();

        $productPricesContainer->setFinalPrice($finalPrice);

        return $productPricesContainer;
    }
}
