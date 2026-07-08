<?php

declare(strict_types=1);

namespace MageSuite\Discount\Test\Integration\Helper;

/**
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class DiscountHelperTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Catalog\Model\Product $product;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    protected ?\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory;
    protected ?\Magento\Catalog\Model\Config $catalogConfig;
    protected ?\MageSuite\Discount\Helper\DiscountFactory $discountHelperFactory;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->product = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->productCollectionFactory = $this->objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);
        $this->catalogConfig = $this->objectManager->create(\Magento\Catalog\Model\Config::class);

        $this->discountHelperFactory = $this->objectManager->get(\MageSuite\Discount\Helper\DiscountFactory::class);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoDataFixture MageSuite_Discount::Test/Integration/_files/configurable_product.php
     */
    public function testItReturnsCorrectDataForConfigurableProducts(): void
    {
        $configurableProductSku = 'configurable';

        $productFromRepository = $this->getFromRepository($configurableProductSku);
        $productFromCollection = $this->getFromCollection($configurableProductSku);

        $this->itReturnsCorrectConfigurableDiscounts($productFromRepository);
        $this->itReturnsCorrectSalePercentage($productFromRepository);

        $this->itReturnsCorrectConfigurableDiscounts($productFromCollection);
        $this->itReturnsCorrectSalePercentage($productFromCollection);
    }

    protected function itReturnsCorrectConfigurableDiscounts(\Magento\Catalog\Api\Data\ProductInterface $configurableProduct): void
    {
        $expectedResult = [
            $this->product->getIdBySku('simple_10') => 95,
            $this->product->getIdBySku('simple_20') => 68
        ];

        $configurableDiscounts = $this->getDiscountHelper()->getConfigurableDiscounts($configurableProduct);

        $this->assertEquals($expectedResult, $configurableDiscounts);
    }

    protected function itReturnsCorrectSalePercentage(\Magento\Catalog\Api\Data\ProductInterface $configurableProduct): void
    {
        $salePercentage = $this->getDiscountHelper()->getSalePercentage($configurableProduct);

        $this->assertEquals(95, $salePercentage);
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @magentoDataFixture MageSuite_Discount::Test/Integration/_files/bundle_product.php
     */
    public function testItReturnsCorrectSalePercentageForBundleProduct(): void
    {
        $bundleProductSku = 'bundle-product';

        $productFromRepository = $this->getFromRepository($bundleProductSku);
        $productFromCollection = $this->getFromCollection($bundleProductSku);

        $salePercentage = $this->getDiscountHelper()->getSalePercentage($productFromRepository);
        $this->assertEquals(35, $salePercentage);

        $salePercentage = $this->getDiscountHelper()->getSalePercentage($productFromCollection);
        $this->assertEquals(35, $salePercentage);
    }

    /**
     * @magentoDataFixture MageSuite_Discount::Test/Integration/_files/product_with_tax.php
     * @magentoConfigFixture current_store tax/calculation/price_includes_tax 0
     * @magentoConfigFixture current_store tax/display/type 2
     */
    public function testItReturnsCorrectSalePercentageForProductWithTax(): void
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
     * @magentoDataFixture MageSuite_Discount::Test/Integration/_files/product.php
     * @magentoDataFixture MageSuite_Discount::Test/Integration/_files/catalog_rule.php
     */
    public function testItReturnsCorrectSalePercentageForCatalogRule(): void
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
     * @magentoConfigFixture current_store catalog/frontend/is_special_price_resolver_enabled 1
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoDataFixture MageSuite_Discount::Test/Integration/_files/configurable_product.php
     */
    public function testCorrectFinalPriceFromChildrenSpecialPrice(): void
    {
        $productSku = 'configurable';
        $product = $this->getFromRepository($productSku);

        $salePercentage = $this->getDiscountHelper()->getSalePercentage($product);
        $this->assertEquals(95, $salePercentage);
    }

    /**
     * @magentoConfigFixture current_store catalog/frontend/is_special_price_resolver_enabled 1
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     */
    public function testCorrectFinalPriceFromChildrenWithoutSpecialPrice(): void
    {
        $productSku = 'configurable';
        $product = $this->getFromRepository($productSku);

        $salePercentage = $this->getDiscountHelper()->getSalePercentage($product);

        $this->assertEquals(0, $salePercentage);
    }

    /**
     * @magentoConfigFixture current_store catalog/frontend/is_special_price_resolver_enabled 1
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoDataFixture MageSuite_Discount::Test/Integration/_files/configurable_product_with_dates.php
     */
    public function testCorrectFinalPriceFromChildrenSpecialPriceWithDates(): void
    {
        $productSku = 'configurable';
        $product = $this->getFromRepository($productSku);

        $salePercentage = $this->getDiscountHelper()->getSalePercentage($product);
        $this->assertEquals(68, $salePercentage);
    }

    /**
     * @magentoConfigFixture current_store catalog/frontend/is_special_price_resolver_enabled 1
     * @magentoDataFixture MageSuite_Discount::Test/Integration/_files/product.php
     * @magentoDataFixture MageSuite_Discount::Test/Integration/_files/product_with_tax.php
     */
    public function testSalePercentageSimpleProductsWithSpecialPriceResolver(): void
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
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoDataFixture MageSuite_Discount::Test/Integration/_files/configurable_product.php
     * @magentoConfigFixture current_store catalog/frontend/sale_percentage_calculation_type biggest_difference_between_same_simple_special_and_regular_price
     */
    public function testItReturnsCorrectDataForConfigurableProductsWithAlternativeDiscountCalculationType(): void
    {
        $configurableProductSku = 'configurable';

        $productFromRepository = $this->getFromRepository($configurableProductSku);
        $productFromCollection = $this->getFromCollection($configurableProductSku);

        $this->itReturnsCorrectConfigurableDiscountsWithAlternativeDiscountCalculationType($productFromRepository);
        $this->itReturnsCorrectSalePercentageWithAlternativeDiscountCalculationType($productFromRepository);

        $this->itReturnsCorrectConfigurableDiscountsWithAlternativeDiscountCalculationType($productFromCollection);
        $this->itReturnsCorrectSalePercentageWithAlternativeDiscountCalculationType($productFromCollection);
    }

    protected function itReturnsCorrectConfigurableDiscountsWithAlternativeDiscountCalculationType(\Magento\Catalog\Api\Data\ProductInterface $configurableProduct): void
    {
        $expectedResult = [
            $this->product->getIdBySku('simple_10') => 90,
            $this->product->getIdBySku('simple_20') => 68
        ];

        $configurableDiscounts = $this->getDiscountHelper()->getConfigurableDiscounts($configurableProduct);

        $this->assertEquals($expectedResult, $configurableDiscounts);
    }

    protected function itReturnsCorrectSalePercentageWithAlternativeDiscountCalculationType(\Magento\Catalog\Api\Data\ProductInterface $configurableProduct): void
    {
        $salePercentage = $this->getDiscountHelper()->getSalePercentage($configurableProduct);

        $this->assertEquals(90, $salePercentage);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoDataFixture MageSuite_Discount::Test/Integration/_files/configurable_product_out_of_stock.php
     * @magentoConfigFixture current_store catalog/frontend/sale_percentage_calculation_type biggest_difference_between_same_simple_special_and_regular_price
     * @magentoConfigFixture current_store cataloginventory/options/show_out_of_stock 0
     */
    public function testItWorksWithOutOfStockConfigurableProducts(): void
    {
        $configurableProduct = $this->getFromRepository('configurable');

        $discountHelper = $this->discountHelperFactory->create();
        $this->assertEquals(0, $discountHelper->getSalePercentage($configurableProduct));
    }

    /**
     * @magentoDataFixture MageSuite_Discount::Test/Integration/_files/grouped_product.php
     * @magentoConfigFixture current_store catalog/frontend/show_biggest_discount_from_children_of_grouped_product 1
     */
    public function testItReturnCorrectSalePercentageForGroupedProduct(): void
    {
        $groupedProduct = $this->getFromRepository('grouped');

        $discountHelper = $this->discountHelperFactory->create();
        $this->assertEquals(10, $discountHelper->getSalePercentage($groupedProduct));
    }

    /**
     * @magentoDataFixture MageSuite_Discount::Test/Integration/_files/sale_product.php
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('getPercentageForSimpleProduct')]
    public function testItReturnsCorrectPercentage($specialPrice, $specialPriceFrom, $specialPriceTo, $getPrice, $customFinalPrice, $expected): void // phpcs:ignore
    {
        $productStub = $this->prepareProductStubForOnSale($specialPrice, $specialPriceFrom, $specialPriceTo, $getPrice);

        $this->assertEquals($expected, $this->getDiscountHelper()->getSalePercentage($productStub, $customFinalPrice));
    }

    public static function getPercentageForSimpleProduct(): array
    {
        return [
            [100, date('Y-m-d 00:00:00', strtotime('-7 days')), date('Y-m-d 00:00:00', strtotime('+7 days')), 200, null, 50],
            [100, date('Y-m-d 00:00:00', strtotime('+7 days')), date('Y-m-d 00:00:00', strtotime('+17 days')), 200, null, false],
            [100, date('Y-m-d 00:00:00', strtotime('-7 days')), date('Y-m-d 00:00:00', strtotime('-3 days')), 200, null, false],
            [300, date('Y-m-d 00:00:00', strtotime('-7 days')), date('Y-m-d 00:00:00', strtotime('-3 days')), 200, null, false],
            ['', date('Y-m-d 00:00:00', strtotime('-7 days')), date('Y-m-d 00:00:00', strtotime('-3 days')), 200, null, false],
            [150, null, date('Y-m-d 00:00:00', strtotime('+3 days')), 500, null, 70],
            [100, null, date('Y-m-d 00:00:00', strtotime('-3 days')), 200, null, false],
            [10, date('Y-m-d 00:00:00', strtotime('-3 days')), null, 300, null, 97],
            ['', null, null, 200, null, false],
            ['', null, null, 200, 50, 75],
            [100, date('Y-m-d 00:00:00', strtotime('-7 days')), date('Y-m-d 00:00:00', strtotime('+7 days')), 200, 50, 75],
        ];
    }

    protected function prepareProductStubForOnSale($specialPrice, $specialPriceFrom, $specialPriceTo, $getPrice): \Magento\Catalog\Api\Data\ProductInterface // phpcs:ignore
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

    protected function getFromRepository(string $productSku): \Magento\Catalog\Api\Data\ProductInterface
    {
        return $this->productRepository->get($productSku);
    }

    protected function getFromCollection(string $productSku): \Magento\Catalog\Api\Data\ProductInterface
    {
        $collection = $this->productCollectionFactory->create();

        return $collection
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addFieldToFilter('sku', $productSku)
            ->addPriceData()
            ->getFirstItem();
    }

    protected function getDiscountHelper(): \MageSuite\Discount\Helper\Discount
    {
        return $this->discountHelperFactory->create();
    }
}
