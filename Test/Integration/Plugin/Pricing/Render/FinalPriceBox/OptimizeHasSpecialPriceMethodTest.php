<?php

namespace MageSuite\Discount\Test\Integration\Plugin\Pricing\Render\FinalPriceBox;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class OptimizeHasSpecialPriceMethodTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Catalog\Block\Product\View
     */
    protected $productView;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->productView = $this->objectManager->get(\Magento\Catalog\Block\Product\View::class);

        $priceRender = $this->objectManager->get(\Magento\Framework\View\LayoutInterface::class)->getBlock('product.price.render.default');

        if (!$priceRender) {
            $this->objectManager->get(
                \Magento\Framework\View\LayoutInterface::class
            )->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                [
                    'data' => [
                        'price_render_handle' => 'catalog_product_prices',
                    ],
                ]
            );
        }
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     */
    public function testItReturnCorrectHtmlForConfigurableWithoutSpecialPrice()
    {
        $configurableProductSku = 'configurable';
        $configurableProduct = $this->productRepository->get($configurableProductSku);

        $priceHtml = $this->getPriceHtmlForProduct($configurableProduct);

        $this->assertContains('data-price-type="finalPrice', $priceHtml);
        $this->assertNotContains('data-price-type="oldPrice"', $priceHtml);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoDataFixture loadConfigurableProduct
     */
    public function testItReturnCorrectHtmlForConfigurableWithSpecialPrice()
    {
        $configurableProductSku = 'configurable';
        $configurableProduct = $this->productRepository->get($configurableProductSku);

        $priceHtml = $this->getPriceHtmlForProduct($configurableProduct);

        $this->assertContains('data-price-type="finalPrice', $priceHtml);
        $this->assertContains('data-price-type="oldPrice"', $priceHtml);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadProduct
     */
    public function testItReturnCorrectHtmlForProductWithSpecialPrice()
    {
        $productSku = 'product_with_tax';
        $product = $this->productRepository->get($productSku);

        $priceHtml = $this->getPriceHtmlForProduct($product);

        $this->assertContains('data-price-type="finalPrice', $priceHtml);
        $this->assertContains('data-price-type="oldPrice"', $priceHtml);
    }

    protected function getPriceHtmlForProduct($product)
    {
        $priceCode = 'final_price';

        return $this->productView->getProductPriceHtml(
            $product,
            $priceCode,
            \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
            ['area' => 'frontend']
        );
    }

    public static function loadConfigurableProduct()
    {
        require __DIR__ . '/../../../../_files/configurable_product.php';
    }

    public static function loadProduct()
    {
        require __DIR__ . '/../../../../_files/product_with_tax.php';
    }
}
