<?php

namespace MageSuite\Discount\Model\ProductPriceResolver;

class Simple extends ProductPriceResolver implements ProductPriceResolverInterface
{
    public function isApplicable($productTypeId)
    {
        return $productTypeId == \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE;
    }

    public function getPrices($product, $finalPrice)
    {
        if ($product->hasData('final_price') && $product->getData('final_price') && $product->hasData('price')) {
            $regularPrice = $product->getData('price');
            $finalPrice = $finalPrice ?? $product->getData('final_price');
        } else {
            $regularPrice = $product->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE)->getAmount()->getValue();
            $finalPrice = $finalPrice ?? $product->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)->getAmount()->getValue();
        }

        $productPricesContainer = $this->getProductPricesContainer();

        $productPricesContainer
            ->setRegularPrice($regularPrice)
            ->setFinalPrice($finalPrice);

        return $productPricesContainer;
    }
}
