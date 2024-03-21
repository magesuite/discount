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
        $productPricesContainer = $this->getProductPricesContainer();
        $regularPrice = $this->getRegularPrice($product);
        $productPricesContainer->setRegularPrice($regularPrice);

        if ($this->configuration->isSpecialPriceResolverEnabled()) {
            if (!$product->getSpecialPrice()) {
                $productPricesContainer
                    ->setFinalPrice($regularPrice);

                return $productPricesContainer;
            }
        }

        $finalPrice = $this->getFinalPrice($product, $finalPrice);
        $productPricesContainer
            ->setFinalPrice($finalPrice);

        return $productPricesContainer;
    }

    protected function getRegularPrice($product)
    {
        if ($product->hasData('final_price') && $product->getData('final_price') && $product->hasData('price')) {
            $regularPrice = $product->getData('price');
        } else {
            $regularPrice = $product->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE)->getAmount()->getValue();
        }

        return $regularPrice;
    }

    protected function getFinalPrice($product, $finalPrice)
    {
        if ($product->hasData('final_price') && $product->getData('final_price') && $product->hasData('price')) {
            $finalPrice = $finalPrice ?? $product->getData('final_price');
        } else {
            $finalPrice = $finalPrice ?? $product->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)->getAmount()->getValue();
        }

        return $finalPrice;
    }
}
