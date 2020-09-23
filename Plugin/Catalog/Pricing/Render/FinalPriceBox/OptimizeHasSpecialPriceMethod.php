<?php

namespace MageSuite\Discount\Plugin\Catalog\Pricing\Render\FinalPriceBox;

class OptimizeHasSpecialPriceMethod
{
    public function aroundHasSpecialPrice(\Magento\Catalog\Pricing\Render\FinalPriceBox $subject, \Closure $proceed)
    {
        $product = $subject->getSaleableItem();

        if ($product->hasData('price') && $product->hasData('final_price') &&
            (float)$product->getData('price') && (float)$product->getData('final_price')
        ) {
            return $product->getData('final_price') < $product->getData('price');
        }

        return $proceed();
    }
}
