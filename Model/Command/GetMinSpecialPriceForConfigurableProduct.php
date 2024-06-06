<?php

namespace MageSuite\Discount\Model\Command;

class GetMinSpecialPriceForConfigurableProduct
{
    protected \MageSuite\Discount\Model\AddChildrenWithPricesToLoadedItems $addChildrenWithPricesToLoadedItems;
    protected \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone;

    public function __construct(
        \MageSuite\Discount\Model\AddChildrenWithPricesToLoadedItems $addChildrenWithPricesToLoadedItems,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->addChildrenWithPricesToLoadedItems = $addChildrenWithPricesToLoadedItems;
        $this->timezone = $timezone;
    }

    public function execute(\Magento\Catalog\Api\Data\ProductInterface $product, float $regularPrice): float
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

    protected function findMinPrice(array $childrenProducts, float $regularPrice): float
    {
        $specialPriceMinimum = $regularPrice;

        foreach ($childrenProducts as $childProduct) {
            if (!$childProduct->getSpecialPrice()) {
                continue;
            }

            if (!$this->timezone->isScopeDateInInterval(null, $childProduct->getSpecialFromDate(), $childProduct->getSpecialToDate())) {
                continue;
            };

            $specialPriceMinimum = min($specialPriceMinimum, $childProduct->getSpecialPrice());
        }

        return $specialPriceMinimum;
    }
}
