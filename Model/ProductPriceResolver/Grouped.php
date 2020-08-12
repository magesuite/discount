<?php

namespace MageSuite\Discount\Model\ProductPriceResolver;

class Grouped extends ProductPriceResolver implements ProductPriceResolverInterface
{
    public function isApplicable($productTypeId)
    {
        return $productTypeId == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE;
    }

    public function getPrices($product, $finalPrice)
    {
        $minProduct = $product->getPriceInfo()->getPrice(\Magento\GroupedProduct\Pricing\Price\FinalPrice::PRICE_CODE)->getMinProduct();

        if (!$finalPrice) {
            $finalPrice = $minProduct->getData('final_price') ? $minProduct->getData('final_price') : $minProduct->getFinalPrice();
        }

        $productPricesContainer = $this->getProductPricesContainer();

        $productPricesContainer
            ->setRegularPrice($minProduct->getData('price'))
            ->setFinalPrice($finalPrice);

        return $productPricesContainer;
    }
}
