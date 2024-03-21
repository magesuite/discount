<?php

namespace MageSuite\Discount\Model\Command;

class GetMaxPriceForConfigurableProduct
{
    protected \MageSuite\Discount\Model\AddChildrenWithPricesToLoadedItems $addChildrenWithPricesToLoadedItems;

    public function __construct(\MageSuite\Discount\Model\AddChildrenWithPricesToLoadedItems $addChildrenWithPricesToLoadedItems)
    {
        $this->addChildrenWithPricesToLoadedItems = $addChildrenWithPricesToLoadedItems;
    }

    public function execute($product)
    {
        if ($product->getData('origins_from_collection') !== null) {
            $this->addChildrenWithPricesToLoadedItems->execute($product->getData('origins_from_collection'));
        }

        $childrenProducts = $product->getChildrenWithPrices();

        if (empty($childrenProducts)) {
            return $this->getMaxPriceFromPriceInfoModel($product);
        }

        $maxPrice = null;

        foreach ($childrenProducts as $childProduct) {
            if (!$childProduct->hasData('price')) {
                continue;
            }

            $maxPrice = max($maxPrice, (float)$childProduct->getData('price'));
        }

        if (!$maxPrice) {
            return $this->getMaxPriceFromPriceInfoModel($product);
        }

        return $maxPrice;
    }

    protected function getMaxPriceFromPriceInfoModel($product)
    {
        $maxRegularAmount = $product->getPriceInfo()->getPrice(\Magento\ConfigurableProduct\Pricing\Price\ConfigurableRegularPrice::PRICE_CODE)->getMaxRegularAmount();

        if (!$maxRegularAmount) {
            return null;
        }

        return $maxRegularAmount->getValue();
    }
}
