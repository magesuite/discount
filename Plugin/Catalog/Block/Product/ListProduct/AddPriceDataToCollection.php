<?php

namespace MageSuite\Discount\Plugin\Catalog\Block\Product\ListProduct;

class AddPriceDataToCollection
{
    public function afterGetLoadedProductCollection(\Magento\Catalog\Block\Product\ListProduct $subject, $result)
    {
        $isUsingPriceIndex = $result->getLimitationFilters()->isUsingPriceIndex();

        if (!$isUsingPriceIndex) {
            $result->addPriceData();
        }

        return $result;
    }
}
