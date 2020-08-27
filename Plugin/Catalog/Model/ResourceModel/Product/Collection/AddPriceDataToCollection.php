<?php

namespace MageSuite\Discount\Plugin\Catalog\Model\ResourceModel\Product\Collection;

class AddPriceDataToCollection
{
    public function beforeLoad(\Magento\Catalog\Model\ResourceModel\Product\Collection $subject, $printQuery = false, $logQuery = false)
    {
        $isUsingPriceIndex = $subject->getLimitationFilters()->isUsingPriceIndex();
        $storeId = $subject->getStoreId();

        if ($storeId && !$isUsingPriceIndex) {
            $subject->addPriceData();
        }

        return [$printQuery, $logQuery];
    }
}
