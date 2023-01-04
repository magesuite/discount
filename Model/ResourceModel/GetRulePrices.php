<?php

declare(strict_types=1);

namespace MageSuite\Discount\Model\ResourceModel;

class GetRulePrices
{
    protected \Magento\Framework\App\ResourceConnection $resource;

    public function __construct(\Magento\Framework\App\ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @see \Magento\CatalogRule\Model\ResourceModel\Rule::getRulePrices
     */
    public function getRulePrices(array $productIds): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($connection->getTableName('catalogrule_product_price'),
                ['product_id', 'rule_date', 'website_id', 'customer_group_id', 'rule_price'])
            ->where('product_id IN(?)', $productIds, \Zend_Db::INT_TYPE);

        return $connection->fetchAll($select);
    }
}
