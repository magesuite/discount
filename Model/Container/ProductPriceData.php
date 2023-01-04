<?php

declare(strict_types=1);

namespace MageSuite\Discount\Model\Container;

class ProductPriceData
{
    protected \MageSuite\Discount\Model\ResourceModel\GetRulePrices $tableDataProvider;

    /**
     * @var array - array with fallback values in case if there is no data for product in database.
     * [
     *   (int) product_id => value,
     * ]
     */
    protected array $missingProductValues = [];

    /**
     * @var array - multidimensional array used to store catalogrule_product_price table data in easily-accessible form.
     * [
     *   (int) product_id => [
     *     (int) website_id => [
     *       (int) customer_group_id => [
     *         (string) rule_date => value,
     *       ],
     *     ],
     *   ],
     * ]
     */
    protected array $rulePriceData = [];

    public function __construct(
        \MageSuite\Discount\Model\ResourceModel\GetRulePrices $tableDataProvider
    ) {
        $this->tableDataProvider = $tableDataProvider;
    }

    public function initProducts(array $productIds): void
    {
        $tableData = $this->tableDataProvider->getRulePrices($productIds);

        foreach ($tableData as $item) {
            $this->rulePriceData[$item['product_id']][$item['website_id']][$item['customer_group_id']][$item['rule_date']] = $item['rule_price'];
        }

        $missingProductIds = array_diff($productIds, array_column($tableData, 'product_id'));

        if (empty($missingProductIds)) {
            return;
        }

        $this->missingProductValues += array_reduce($missingProductIds, function ($result, $productId) {
            $result[$productId] = false;
            return $result;
        }, []);
    }

    public function getRulePrices(\DateTimeInterface $date, int $websiteId, int $customerGroupId, array $productIds): array
    {
        $ruleDate = $date->format('Y-m-d');
        $result = [];

        foreach ($productIds as $productId) {
            $rulePrice = $this->rulePriceData[$productId][$websiteId][$customerGroupId][$ruleDate] ?? $this->missingProductValues[$productId] ?? null;

            if (isset($rulePrice)) {
                $result[$productId] = $rulePrice;
            }
        }

        return $result;
    }
}
