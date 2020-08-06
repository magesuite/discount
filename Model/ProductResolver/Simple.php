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
        if (!$finalPrice) {
            $finalPrice = $product->getData('final_price') ? $product->getData('final_price') : $product->getFinalPrice();
        }

        return [
            'regular_price' => $product->getData('price'),
            'final_price' => $finalPrice
        ];
    }
}
