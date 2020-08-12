<?php

namespace MageSuite\Discount\Model\ProductPriceResolver;

class Configurable extends ProductPriceResolver implements ProductPriceResolverInterface
{
    public function isApplicable($productTypeId)
    {
        return $productTypeId == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;
    }

    public function getPrices($product, $finalPrice)
    {
        $regularPrice = $product->getPriceInfo()->getPrice(\Magento\ConfigurableProduct\Pricing\Price\ConfigurableRegularPrice::PRICE_CODE)->getMaxRegularAmount()->getValue();

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
