<?php

namespace MageSuite\Discount\Model\ProductResolver;

class Bundle implements ProductResolverInterface
{
    public function isApplicable($productTypeId)
    {
        return $productTypeId == \Magento\Bundle\Model\Product\Type::TYPE_CODE;
    }

    public function getPrices($product, $finalPrice)
    {
        return [
            'regular_price' => 100,
            'final_price' => (100 - $product->getSpecialPrice())
        ];
    }
}
