<?php

namespace MageSuite\Discount\Model\ProductResolver;

class Configurable implements ProductResolverInterface
{
    public function isApplicable($productTypeId)
    {
        return $productTypeId == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;
    }

    public function getPrices($product, $finalPrice)
    {
        return [
            'regular_price' => $product->getData('max_price'),
            'final_price' => $finalPrice ? $finalPrice : $product->getData('min_price')
        ];
    }
}
