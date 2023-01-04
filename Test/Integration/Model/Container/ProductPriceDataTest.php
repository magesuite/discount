<?php

declare(strict_types=1);

namespace MageSuite\Discount\Test\Integration\Model\Container;

class ProductPriceDataTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\CatalogRule\Model\ResourceModel\Rule $originalGetRulePrices = null;
    protected ?\MageSuite\Discount\Model\Container\ProductPriceData $container = null;
    protected ?int $productId = null;
    protected ?int $websiteId = null;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->container = $objectManager->get(\MageSuite\Discount\Model\Container\ProductPriceData::class);
        $this->originalGetRulePrices = $objectManager->get(\Magento\CatalogRule\Model\ResourceModel\Rule::class);

        $productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->productId = (int)$productRepository->get('simple')->getId();

        $websiteRepository = $objectManager->get(\Magento\Store\Api\WebsiteRepositoryInterface::class);
        $this->websiteId = (int)$websiteRepository->get('base')->getId();
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoDataFixture Magento/CatalogRule/_files/rule_adjust_final_price_to_discount_value_not_logged_user.php
     */
    public function testHasRulePriceAndFallbackData(): void
    {
        $this->container->initProducts([101, 102, 103, $this->productId]);

        $data = $this->container->getRulePrices(
            new \DateTime(),
            $this->websiteId,
            \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
            [101, 102, 103, $this->productId]
        );

        $expected = [
            101 => false,
            102 => false,
            103 => false,
            $this->productId => '10.000000',
        ];

        $this->assertEquals($expected, $data);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoDataFixture Magento/CatalogRule/_files/rule_adjust_final_price_to_discount_value_not_logged_user.php
     */
    public function testCompareResults()
    {
        $this->container->initProducts([$this->productId]);

        $data = $this->container->getRulePrices(
            new \DateTime(),
            $this->websiteId,
            \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
            [$this->productId]
        );

        $originalData = $this->originalGetRulePrices->getRulePrices(
            new \DateTime(),
            $this->websiteId,
            \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
            [$this->productId]
        );

        $this->assertEquals($originalData, $data);
    }
}
