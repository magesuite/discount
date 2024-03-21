<?php

namespace MageSuite\Discount\Test\Integration\Plugin\Catalog\Model\ResourceModel\Product\Collection;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class PreloadChildrenWithPricesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \MageSuite\Discount\Helper\Discount|mixed
     */
    protected $discountHelper;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->productCollectionFactory = $this->objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);
        $this->discountHelper = $this->objectManager->create(\MageSuite\Discount\Helper\Discount::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoDataFixture loadConfigurableProduct
     */
    public function testChildrenWithPricesAreNotAddedToCollectionByDefault()
    {
        $productSku = 'configurable';

        $collection = $this->getProductCollection($productSku);

        foreach ($collection as $item) {
            $this->assertNull($item->getChildrenWithPrices());
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoDataFixture loadConfigurableProduct
     */
    public function testChildrenWithPricesAreAddedToCollectionWhenSaleRelatedLogicIsExecuted()
    {
        $productSku = 'configurable';

        $collection = $this->getProductCollection($productSku);

        foreach ($collection as $item) {
            $this->discountHelper->isOnSale($item);

            $this->assertNotNull($item->getChildrenWithPrices());

            foreach ($item->getChildrenWithPrices() as $childProduct) {
                $this->assertTrue($childProduct->hasData('price'));
                $this->assertTrue($childProduct->hasData('final_price'));
            }
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoDataFixture loadConfigurableProduct
     */
    public function testChildrenWithPricesAreAddedToFirstItem()
    {
        $productSku = 'configurable';

        $collection = $this->getProductCollection($productSku);
        $firstItem = $collection->getFirstItem();
        $this->discountHelper->isOnSale($firstItem);

        $this->assertNotNull($firstItem->getChildrenWithPrices());
    }

    protected function getProductCollection($productSku)
    {
        $collection = $this->productCollectionFactory->create();

        return $collection
            ->addFieldToFilter('sku', $productSku);
    }

    public static function loadConfigurableProduct()
    {
        require __DIR__ . '/../../../../../../_files/configurable_product.php';
    }
}
