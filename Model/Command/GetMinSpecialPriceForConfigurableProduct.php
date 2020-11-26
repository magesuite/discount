<?php

namespace MageSuite\Discount\Model\Command;

class GetMinSpecialPriceForConfigurableProduct
{
    public function execute($product, $regularPrice)
    {
        $childrenProducts = $product->getChildrenWithPrices();

        if (empty($childrenProducts)) {
            $childrenProducts = $product->getTypeInstance()->getUsedProducts($product);
        }

        if (!empty($childrenProducts)) {
            return $this->findMinPrice($childrenProducts, $regularPrice);
        }

        return $regularPrice;
    }

    protected function findMinPrice($childrenProducts, $regularPrice)
    {
        $specialPriceMinimum = $regularPrice;

        foreach ($childrenProducts as $childProduct) {
            if(!$childProduct->getSpecialPrice()) {
                continue;
            }

            $specialPriceMinimum = min($specialPriceMinimum, $childProduct->getSpecialPrice());
        }

        return $specialPriceMinimum;
    }
}
