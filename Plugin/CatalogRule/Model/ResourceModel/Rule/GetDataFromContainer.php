<?php

declare(strict_types=1);

namespace MageSuite\Discount\Plugin\CatalogRule\Model\ResourceModel\Rule;

class GetDataFromContainer
{
    protected \MageSuite\Discount\Model\Container\ProductPriceData $container;

    public function __construct(\MageSuite\Discount\Model\Container\ProductPriceData $container)
    {
        $this->container = $container;
    }

    public function aroundGetRulePrices(
        \Magento\CatalogRule\Model\ResourceModel\Rule $subject,
        callable $proceed,
        \DateTimeInterface $date,
        $websiteId,
        $customerGroupId,
        array $productIds
    ): array {
        $productIdsInteger = array_map('intval', $productIds);
        $containerData = $this->container->getRulePrices(
            $date,
            (int)$websiteId,
            (int)$customerGroupId,
            $productIdsInteger
        );

        $productIdsDiff = array_diff($productIdsInteger, array_keys($containerData));

        if (empty($productIdsDiff)) {
            return $containerData;
        }

        $missingData = $proceed($date, $websiteId, $customerGroupId, $productIdsDiff);

        return $containerData + $missingData;
    }
}
