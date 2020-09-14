<?php

namespace MageSuite\Discount\Model\ProductPriceResolver;

class Configurable extends ProductPriceResolver implements ProductPriceResolverInterface
{
    /**
     * @var \MageSuite\Discount\Model\Command\GetMaxPriceForConfigurableProduct
     */
    protected $getMaxPriceForConfigurableProduct;

    public function __construct(
        \MageSuite\Discount\Api\Data\ProductPricesInterfaceFactory $productPricesFactory,
        \MageSuite\Discount\Model\Command\GetMaxPriceForConfigurableProduct $getMaxPriceForConfigurableProduct
    ) {

        parent::__construct($productPricesFactory);

        $this->getMaxPriceForConfigurableProduct = $getMaxPriceForConfigurableProduct;
    }

    public function isApplicable($productTypeId)
    {
        return $productTypeId == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;
    }

    public function getPrices($product, $finalPrice)
    {
        $regularPrice = $this->getMaxPriceForConfigurableProduct->execute($product);

        if (!$finalPrice) {
            $finalPrice = $product->hasData('min_price') ?
                $product->getData('min_price') :
                $product->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)->getAmount()->getValue();
        }

        $productPricesContainer = $this->getProductPricesContainer();

        $productPricesContainer
            ->setRegularPrice($regularPrice)
            ->setFinalPrice($finalPrice);

        return $productPricesContainer;
    }
}
