<?php

namespace MageSuite\Discount\Model\ProductResolver;

class Grouped implements ProductResolverInterface
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

        return [
            'regular_price' => $minProduct->getData('price'),
            'final_price' => $finalPrice
        ];
    }
}
