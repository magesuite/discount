<?php

namespace MageSuite\Discount\Test\Integration\Helper;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class DiscountHelperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * @var \Magento\Catalog\Model\ProductFrontendAction
     */
    protected $productFrontendAction;

    /**
     * @var \MageSuite\Discount\Helper\DiscountFactory
     */
    protected $discountHelperFactory;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->product = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->productCollectionFactory = $this->objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);
        $this->catalogConfig = $this->objectManager->create(\Magento\Catalog\Model\Config::class);
        $this->productFrontendAction = $this->objectManager->create( \Magento\Catalog\Model\ProductFrontendAction::class);

        $this->discountHelperFactory = $this->objectManager->get(\MageSuite\Discount\Helper\DiscountFactory::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoDataFixture loadConfigurableProduct
     */
    public function testItReturnsCorrectDataForConfigurableProducts()
    {
        $configurableProductSku = 'configurable';

        $productFromRepository = $this->getFromRepository($configurableProductSku);
        $productFromCollection = $this->getFromCollection($configurableProductSku);

        $this->itReturnsCorrectConfigurableDiscounts($productFromRepository);
        $this->itReturnsCorrectSalePercentage($productFromRepository);

        $this->itReturnsCorrectConfigurableDiscounts($productFromCollection);
        $this->itReturnsCorrectSalePercentage($productFromCollection);
    }

    protected function itReturnsCorrectConfigurableDiscounts($configurableProduct)
    {
        $expectedResult = [
            $this->product->getIdBySku('simple_10') => 95,
            $this->product->getIdBySku('simple_20') => 68
        ];

        $configurableDiscounts = $this->getDiscountHelper()->getConfigurableDiscounts($configurableProduct);

        $this->assertEquals($expectedResult, $configurableDiscounts);
    }

    protected function itReturnsCorrectSalePercentage($configurableProduct)
    {
        $salePercentage = $this->getDiscountHelper()->getSalePercentage($configurableProduct);

        $this->assertEquals(95, $salePercentage);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @magentoDataFixture loadBundleProduct
     */
    public function testItReturnsCorrectSalePercentageForBundleProduct()
    {
        $bundleProductSku = 'bundle-product';

        $productFromRepository = $this->getFromRepository($bundleProductSku);
        $productFromCollection = $this->getFromCollection($bundleProductSku);

        $salePercentage = $this->getDiscountHelper()->getSalePercentage($productFromRepository);
        $this->assertEquals(65, $salePercentage);

        $salePercentage = $this->getDiscountHelper()->getSalePercentage($productFromCollection);
        $this->assertEquals(65, $salePercentage);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadProductWithTax
     * @magentoConfigFixture current_store tax/calculation/price_includes_tax 0
     * @magentoConfigFixture current_store tax/display/type 2
     */
    public function testItReturnsCorrectSalePercentageForProductWithTax()
    {
        $productWithTaxSku = 'product_with_tax';

        $productFromRepository = $this->getFromRepository($productWithTaxSku);
        $productFromCollection = $this->getFromCollection($productWithTaxSku);

        $salePercentage = $this->getDiscountHelper()->getSalePercentage($productFromRepository);
        $this->assertEquals(50, $salePercentage);

        $salePercentage = $this->getDiscountHelper()->getSalePercentage($productFromCollection);
        $this->assertEquals(50, $salePercentage);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadProduct
     * @magentoDataFixture loadCatalogRule
     */
    public function testItReturnsCorrectSalePercentageForCatalogRule()
    {
        $productSku = 'product';

        $productFromRepository = $this->getFromRepository($productSku);
        $productFromCollection = $this->getFromCollection($productSku);

        $salePercentage = $this->getDiscountHelper()->getSalePercentage($productFromRepository);
        $this->assertEquals(20, $salePercentage);

        $salePercentage = $this->getDiscountHelper()->getSalePercentage($productFromCollection);
        $this->assertEquals(20, $salePercentage);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/frontend/is_special_price_resolver_enabled 1
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoDataFixture loadConfigurableProduct
     */
    public function testCorrectFinalPriceFromChildrenSpecialPrice()
    {
        $productSku = 'configurable';
        $product = $this->getFromRepository($productSku);

        $salePercentage = $this->getDiscountHelper()->getSalePercentage($product);
        $this->assertEquals(95, $salePercentage);
    }


    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/frontend/is_special_price_resolver_enabled 1
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     */
    public function testCorrectFinalPriceFromChildrenWithoutSpecialPrice()
    {
        $productSku = 'configurable';
        $product = $this->getFromRepository($productSku);

        $salePercentage = $this->getDiscountHelper()->getSalePercentage($product);

        $this->assertEquals(0, $salePercentage);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/frontend/is_special_price_resolver_enabled 1
     * @magentoDataFixture loadProduct
     * @magentoDataFixture loadProductWithTax
     */
    public function testSalePercentageSimpleProductsWithSpecialPriceResolver()
    {
        $productSku = 'product';
        $productTaxSku = 'product_with_tax';
        $product = $this->getFromRepository($productSku);
        $productTax = $this->getFromRepository($productTaxSku);

        $salePercentageNoSpecialPrice = $this->getDiscountHelper()->getSalePercentage($product);
        $salePercentageWithSpecialPrice = $this->getDiscountHelper()->getSalePercentage($productTax);

        $this->assertEquals(0, $salePercentageNoSpecialPrice);
        $this->assertEquals(50, $salePercentageWithSpecialPrice);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoDataFixture loadConfigurableProduct
     * @magentoConfigFixture current_store catalog/frontend/sale_percentage_calculation_type biggest_difference_between_same_simple_special_and_regular_price
     */
    public function testItReturnsCorrectDataForConfigurableProductsWithAlternativeDiscountCalculationType()
    {
        $configurableProductSku = 'configurable';

        $productFromRepository = $this->getFromRepository($configurableProductSku);
        $productFromCollection = $this->getFromCollection($configurableProductSku);

        $this->itReturnsCorrectConfigurableDiscountsWithAlternativeDiscountCalculationType($productFromRepository);
        $this->itReturnsCorrectSalePercentageWithAlternativeDiscountCalculationType($productFromRepository);

        $this->itReturnsCorrectConfigurableDiscountsWithAlternativeDiscountCalculationType($productFromCollection);
        $this->itReturnsCorrectSalePercentageWithAlternativeDiscountCalculationType($productFromCollection);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoDataFixture loadConfigurableProductOutOfStock
     * @magentoConfigFixture current_store catalog/frontend/sale_percentage_calculation_type biggest_difference_between_same_simple_special_and_regular_price
     * @magentoConfigFixture current_store cataloginventory/options/show_out_of_stock 0
     */
    public function testItWorksWithOutOfStockConfigurableProducts()
    {
        $configurableProductSku = 'configurable';
        $product = $this->getFromRepository($configurableProductSku);

        $discountHelper = $this->discountHelperFactory->create();
        $this->assertEquals(0, $discountHelper->getSalePercentage($product));
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoConfigFixture current_store catalog/frontend/sale_percentage_calculation_type biggest_difference_between_same_simple_special_and_regular_price
     */
    public function testSalePercentageReturnsZeroInsteadOfError()
    {
        $this->productFrontendAction->setTypeId(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);

        $discountHelper = $this->discountHelperFactory->create();
        $this->assertEquals(0, $discountHelper->getSalePercentage($this->productFrontendAction));
    }

    protected function itReturnsCorrectConfigurableDiscountsWithAlternativeDiscountCalculationType($configurableProduct)
    {
        $expectedResult = [
            $this->product->getIdBySku('simple_10') => 90,
            $this->product->getIdBySku('simple_20') => 68
        ];

        $configurableDiscounts = $this->getDiscountHelper()->getConfigurableDiscounts($configurableProduct);

        $this->assertEquals($expectedResult, $configurableDiscounts);
    }

    protected function itReturnsCorrectSalePercentageWithAlternativeDiscountCalculationType($configurableProduct)
    {
        $salePercentage = $this->getDiscountHelper()->getSalePercentage($configurableProduct);

        $this->assertEquals(90, $salePercentage);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadSaleProduct
     * @dataProvider getPercentage
     * @param $specialPrice
     * @param $specialPriceFrom
     * @param $specialPriceTo
     * @param $getPrice
     * @param $getFinalPrice
     * @param $customFinalPrice
     * @param $expected
     */
    public function testItReturnsCorrectPercentage($specialPrice, $specialPriceFrom, $specialPriceTo, $getPrice, $getFinalPrice, $customFinalPrice, $expected)
    {
        $productStub = $this->prepareProductStubForOnSale($specialPrice, $specialPriceFrom, $specialPriceTo, $getPrice, $getFinalPrice);

        $this->assertEquals($expected, $this->getDiscountHelper()->getSalePercentage($productStub, $customFinalPrice));
    }

    public function getPercentage()
    {
        return [
            [100, date('Y-m-d 00:00:00', strtotime('-7 days')), date('Y-m-d 00:00:00', strtotime('+7 days')), 200, 100, null, 50],
            [100, date('Y-m-d 00:00:00', strtotime('+7 days')), date('Y-m-d 00:00:00', strtotime('+17 days')), 200, 100, null, false],
            [100, date('Y-m-d 00:00:00', strtotime('-7 days')), date('Y-m-d 00:00:00', strtotime('-3 days')), 200, 100, null, false],
            [300, date('Y-m-d 00:00:00', strtotime('-7 days')), date('Y-m-d 00:00:00', strtotime('-3 days')), 200, 100, null, false],
            ['', date('Y-m-d 00:00:00', strtotime('-7 days')), date('Y-m-d 00:00:00', strtotime('-3 days')), 200, 100, null, false],
            [150, null, date('Y-m-d 00:00:00', strtotime('+3 days')), 500, 150, null, 70],
            [100, null, date('Y-m-d 00:00:00', strtotime('-3 days')), 200, 100, null, false],
            [10, date('Y-m-d 00:00:00', strtotime('-3 days')), null, 300, 10, null, 97],
            ['', null, null, 200, 100, null, false],
            ['', null, null, 200, 100, 50, 75],
            [100, date('Y-m-d 00:00:00', strtotime('-7 days')), date('Y-m-d 00:00:00', strtotime('+7 days')), 200, 100, 50, 75],
        ];
    }

    protected function prepareProductStubForOnSale($specialPrice, $specialPriceFrom, $specialPriceTo, $getPrice, $getFinalPrice)
    {
        $product = $this->productRepository->get('sale_product');

        $product->setSpecialPrice($specialPrice);
        $product->setSpecialFromDate($specialPriceFrom);
        $product->setSpecialToDate($specialPriceTo);
        $product->setPrice($getPrice);
        $product->save();

        $product->reindex();
        $product->priceReindexCallback();

        return $product;
    }

    protected function getFromRepository($productSku)
    {
        return $this->productRepository->get($productSku);
    }

    protected function getFromCollection($productSku)
    {
        $collection = $this->productCollectionFactory->create();

        return $collection
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addFieldToFilter('sku', $productSku)
            ->addPriceData()
            ->getFirstItem();
    }

    protected function getDiscountHelper()
    {
        return $this->discountHelperFactory->create();
    }

    public static function loadSaleProduct()
    {
        require __DIR__ . '/../_files/sale_product.php';
    }

    public static function loadSaleProductRollback()
    {
        require __DIR__ . '/../_files/sale_product_rollback.php';
    }

    public static function loadConfigurableProduct()
    {
        require __DIR__ . '/../_files/configurable_product.php';
    }

    public static function loadConfigurableProductOutOfStock()
    {
        require __DIR__ . '/../_files/configurable_product_out_of_stock.php';
    }

    public static function loadBundleProduct()
    {
        require __DIR__ . '/../_files/bundle_product.php';
    }

    public static function loadProductWithTax()
    {
        require __DIR__ . '/../_files/product_with_tax.php';
    }

    public static function loadProductWithTaxRollback()
    {
        require __DIR__ . '/../_files/product_with_tax_rollback.php';
    }

    public static function loadProduct()
    {
        require __DIR__ . '/../_files/product.php';
    }

    public static function loadProductRollback()
    {
        require __DIR__ . '/../_files/product_rollback.php';
    }

    public static function loadCatalogRule()
    {
        require __DIR__ . '/../_files/catalog_rule.php';
    }

    public static function loadCatalogRuleRollback()
    {
        require __DIR__ . '/../_files/catalog_rule_rollback.php';
    }
}
