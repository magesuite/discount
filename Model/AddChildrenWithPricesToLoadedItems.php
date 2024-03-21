<?php

namespace MageSuite\Discount\Model;

class AddChildrenWithPricesToLoadedItems
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionProvider
     */
    protected $optionProvider;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionProvider $optionProvider,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        $this->resource = $resource;
        $this->optionProvider = $optionProvider;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function execute($collection)
    {
        if ($collection->hasFlag('children_with_prices_preloaded')) {
            return $collection;
        }

        $collection->setFlag('children_with_prices_preloaded', true);

        $productIds = [];

        $products = $collection->getItems();

        foreach ($products as $product) {
            if ($product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                continue;
            }

            $productIds[] = $product->getEntityId();
        }

        if (empty($productIds)) {
            return $collection;
        }

        $childrenProductData = $this->getChildProductData($productIds);
        $childrenIds = $childrenProductData->getChildrenIds();

        if (empty($childrenIds)) {
            return $collection;
        }

        $simpleProductsWithPrices = $this->getSimpleProductsWithPrices($childrenIds);
        $parentChildMap = $childrenProductData->getParentChildMap();

        foreach ($products as $item) {
            $productId = $item->getId();

            if (!isset($parentChildMap[$productId])) {
                continue;
            }

            $children = [];

            foreach ($parentChildMap[$productId] as $childrenId) {
                $child = $simpleProductsWithPrices->getItemById($childrenId);

                if (!$child) {
                    continue;
                }

                $children[] = $child;
            }

            $item->setChildrenWithPrices($children);
        }

        return $collection;
    }

    protected function getChildProductData($productIds)
    {
        $result = new \Magento\Framework\DataObject([
            'children_ids' => null,
            'parent_child_map' => null
        ]);

        $connection = $this->resource->getConnection();

        $configurableRelationsTableName = $connection->getTableName('catalog_product_super_link');
        $productTable = $connection->getTableName('catalog_product_entity');

        $select = $connection
            ->select()
            ->from(['l' => $configurableRelationsTableName], ['product_id', 'parent_id'])
            ->join(['p' => $productTable], 'p.' . $this->optionProvider->getProductEntityLinkField() . ' = l.parent_id', ['entity_id'])
            ->join(['e' => $productTable], 'e.entity_id = l.product_id AND e.required_options = 0', [])
            ->where('p.entity_id IN (?)', $productIds);

        $data = $connection->fetchAll($select);

        if (empty($data) || !is_array($data)) {
            return $result;
        }

        $childrenIds = [];
        $parentChildMap = [];

        foreach ($data as $key => $value) {
            $parentId = $value['entity_id'];
            $childId = $value['product_id'];

            $childrenIds[] = $childId;
            $parentChildMap[$parentId][] = $childId;
        }

        $result->setChildrenIds($childrenIds);
        $result->setParentChildMap($parentChildMap);

        return $result;
    }

    protected function getSimpleProductsWithPrices($productIds)
    {
        $collection = $this->productCollectionFactory->create();

        return $collection
            ->addFieldToFilter($this->optionProvider->getProductEntityLinkField(), ['in' => $productIds])
            ->setFlag('children_with_prices_preloaded', true)
            ->addPriceData()
            ->load();
    }
}
