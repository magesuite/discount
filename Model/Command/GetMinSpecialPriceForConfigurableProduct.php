<?php

namespace MageSuite\Discount\Model\Command;

class GetMinSpecialPriceForConfigurableProduct
{
    protected \MageSuite\Discount\Model\AddChildrenWithPricesToLoadedItems $addChildrenWithPricesToLoadedItems;

    public function __construct(\MageSuite\Discount\Model\AddChildrenWithPricesToLoadedItems $addChildrenWithPricesToLoadedItems)
    {
        $this->addChildrenWithPricesToLoadedItems = $addChildrenWithPricesToLoadedItems;
    }

    public function execute($product, $regularPrice)
    {
        if ($product->getData('origins_from_collection') !== null) {
            $this->addChildrenWithPricesToLoadedItems->execute($product->getData('origins_from_collection'));
        }

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
            if (!$childProduct->getSpecialPrice()) {
                continue;
            }

            $specialPriceMinimum = min($specialPriceMinimum, $childProduct->getSpecialPrice());
        }

        return $specialPriceMinimum;
    }
}
