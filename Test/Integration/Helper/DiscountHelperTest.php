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
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \MageSuite\Discount\Helper\Discount
     */
    protected $discountHelper;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $magentoProductMetadata;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->discountHelper = $this->objectManager->get(\MageSuite\Discount\Helper\Discount::class);
        $this->magentoProductMetadata = $this->objectManager->get(\Magento\Framework\App\ProductMetadataInterface::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoDataFixture loadConfigurableProduct
     */
    public function testItReturnsCorrectDataForConfigurableProducts()
    {
        $configurableProductSku = 'configurable';
        $configurableProduct = $this->productRepository->get($configurableProductSku);

        $this->itReturnsCorrectConfigurableDiscounts($configurableProduct);
        $this->itReturnsCorrectSalePercentage($configurableProduct);
    }

    protected function itReturnsCorrectConfigurableDiscounts($configurableProduct)
    {
        $configurableDiscounts = $this->discountHelper->getConfigurableDiscounts($configurableProduct);

        $this->assertEquals([10 => 90, 20 => 68], $configurableDiscounts);
    }

    protected function itReturnsCorrectSalePercentage($configurableProduct)
    {
        $salePercentage = $this->discountHelper->getSalePercentage($configurableProduct);

        $this->assertEquals(90, $salePercentage);
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
        $bundleProduct = $this->productRepository->get($bundleProductSku);

        $salePercentage = $this->discountHelper->getSalePercentage($bundleProduct);

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
        $productWithTax = $this->productRepository->get($productWithTaxSku);

        $salePercentage = $this->discountHelper->getSalePercentage($productWithTax);

        $this->assertEquals(50, $salePercentage);
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

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadSaleProductFixture
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

        $this->assertEquals($expected, $this->discountHelper->getSalePercentage($productStub, $customFinalPrice));
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

    public static function loadSaleProductFixture()
    {
        require __DIR__ . '/../_files/sale_product.php';
    }

    public static function loadSaleProductFixtureRollback()
    {
        require __DIR__ . '/../_files/sale_product.php';
    }

    public static function loadConfigurableProduct()
    {
        require __DIR__ . '/../_files/configurable_product.php';
    }

    public static function loadBundleProduct()
    {
        require __DIR__ . '/../_files/bundle_product.php';
    }

    public static function loadProductWithTax()
    {
        require __DIR__ . '/../_files/product_with_tax.php';
    }
}
