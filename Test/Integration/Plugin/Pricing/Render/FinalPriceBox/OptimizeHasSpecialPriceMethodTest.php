<?php

declare(strict_types=1);

namespace MageSuite\Discount\Test\Integration\Plugin\Pricing\Render\FinalPriceBox;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class OptimizeHasSpecialPriceMethodTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    protected ?\Magento\Catalog\Block\Product\View $productView;

    public function setUp(): void
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

        $assertContains = method_exists($this, 'assertStringContainsString') ? 'assertStringContainsString' : 'assertContains';
        $assertNotContains = method_exists($this, 'assertStringNotContainsString') ? 'assertStringNotContainsString' : 'assertNotContains';

        $this->$assertContains('data-price-type="finalPrice', $priceHtml);
        $this->$assertNotContains('data-price-type="oldPrice"', $priceHtml);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoDataFixture MageSuite_Discount::Test/Integration/_files/configurable_product.php
     */
    public function testItReturnCorrectHtmlForConfigurableWithSpecialPrice()
    {
        $configurableProductSku = 'configurable';
        $configurableProduct = $this->productRepository->get($configurableProductSku);

        $priceHtml = $this->getPriceHtmlForProduct($configurableProduct);

        $assertContains = method_exists($this, 'assertStringContainsString') ? 'assertStringContainsString' : 'assertContains';

        $this->$assertContains('data-price-type="finalPrice', $priceHtml);
        $this->$assertContains('data-price-type="oldPrice"', $priceHtml);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_Discount::Test/Integration/_files/product_with_tax.php
     */
    public function testItReturnCorrectHtmlForProductWithSpecialPrice()
    {
        $productSku = 'product_with_tax';
        $product = $this->productRepository->get($productSku);

        $priceHtml = $this->getPriceHtmlForProduct($product);

        $assertContains = method_exists($this, 'assertStringContainsString') ? 'assertStringContainsString' : 'assertContains';

        $this->$assertContains('data-price-type="finalPrice', $priceHtml);
        $this->$assertContains('data-price-type="oldPrice"', $priceHtml);
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
}
