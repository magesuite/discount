<?php

namespace MageSuite\Discount\Plugin\ConfigurableProduct\Pricing\Render\FinalPriceBox;

class OptimizeHasSpecialPriceMethod
{
    public function aroundHasSpecialPrice(\Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox $subject, \Closure $proceed)
    {
        $product = $subject->getSaleableItem();

        if ($product->hasData('max_price') && $product->hasData('min_price') &&
            (float)$product->getData('max_price') && (float)$product->getData('max_price')
        ) {
            return $product->getData('min_price') < $product->getData('max_price');
        }

        return $proceed();
    }
}
