<?php

namespace MageSuite\Discount\Model;

class AddChildrenWithPricesToLoadedItems
{
    protected \Magento\Framework\App\ResourceConnection $resource;
    protected \Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionProvider $optionProvider;
    protected \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory;
    protected array $attributesToSelect = [];

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionProvider $optionProvider,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        $attributesToSelect = []
    ) {
        $this->resource = $resource;
        $this->optionProvider = $optionProvider;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->attributesToSelect = $attributesToSelect;
    }

    public function execute(\Magento\Eav\Model\Entity\Collection\AbstractCollection $collection): \Magento\Eav\Model\Entity\Collection\AbstractCollection
    {
        if ($collection->hasFlag('children_with_prices_preloaded')) {
            return $collection;
        }

        $collection->setFlag('children_with_prices_preloaded', true);

        $this->addChildrenWithPricesToProducts($collection->getItems());

        return $collection;
    }

    public function addChildrenWithPricesToProducts(array $products): void
    {
        $productIds = [];

        foreach ($products as $product) {
            if ($product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                continue;
            }

            $productIds[] = $product->getEntityId();
        }

        if (empty($productIds)) {
            return;
        }

        $childrenProductData = $this->getChildProductData($productIds);
        $childrenIds = $childrenProductData->getChildrenIds();

        if (empty($childrenIds)) {
            return;
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
    }

    protected function getChildProductData(array $productIds): \Magento\Framework\DataObject
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

    protected function getSimpleProductsWithPrices(array $productIds): \Magento\Eav\Model\Entity\Collection\AbstractCollection
    {
        $collection = $this->productCollectionFactory->create();

        return $collection
            ->addFieldToFilter('entity_id', ['in' => $productIds])
            ->setFlag('children_with_prices_preloaded', true)
            ->addAttributeToSelect($this->attributesToSelect)
            ->addPriceData()
            ->load();
    }
}
