<?php

namespace MageSuite\Discount\Model\ProductResolver;

class Virtual implements ProductResolverInterface
{
    public function isApplicable($productTypeId)
    {
        return $productTypeId == \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL;
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
