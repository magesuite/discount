<?php

namespace MageSuite\Discount\Model\ProductResolver;

class Simple implements ProductResolverInterface
{
    public function isApplicable($productTypeId)
    {
        return $productTypeId == \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE;
    }

    public function getPrices($product, $finalPrice)
    {
        if ($product->hasData('final_price') && $product->hasData('price')) {
            $regularPrice = $product->getData('price');
            $finalPrice = $finalPrice ?? $product->getData('final_price');
        } else {
            $regularPrice = $product->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE)->getAmount()->getValue();
            $finalPrice = $finalPrice ?? $product->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)->getAmount()->getValue();
        }

        return [
            'regular_price' => $regularPrice,
            'final_price' => $finalPrice
        ];
    }
}
