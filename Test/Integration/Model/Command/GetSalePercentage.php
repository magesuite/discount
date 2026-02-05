<?php

declare(strict_types=1);

namespace MageSuite\Discount\Test\Integration\Model\Command;

class GetSalePercentage extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\Discount\Model\Command\GetSalePercentage $getSalePercentage;
    protected ?\Magento\Catalog\Api\Data\ProductInterface $product;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->getSalePercentage = $objectManager->get(\MageSuite\Discount\Model\Command\GetSalePercentage::class);
        $productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->product = $productRepository->get('simple');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoConfigFixture current_store catalog/frontend/rounding_sale_percentage 1
     */
    public function testDiscountPercentageRoundingHalfUp(): void
    {
        $value = $this->getSalePercentage->execute($this->product, 7.55);
        $this->assertEquals(25, $value);

        $value = $this->getSalePercentage->execute($this->product, 7.54);
        $this->assertEquals(25, $value);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoConfigFixture current_store catalog/frontend/rounding_sale_percentage 2
     */
    public function testDiscountPercentageRoundingHalfDown(): void
    {
        $value = $this->getSalePercentage->execute($this->product, 7.55);
        $this->assertEquals(24, $value);

        $value = $this->getSalePercentage->execute($this->product, 7.54);
        $this->assertEquals(25, $value);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoConfigFixture current_store catalog/frontend/rounding_sale_percentage 0
     */
    public function testDiscountPercentageRoundingDown(): void
    {
        $value = $this->getSalePercentage->execute($this->product, 7.55);
        $this->assertEquals(24, $value);

        $value = $this->getSalePercentage->execute($this->product, 7.54);
        $this->assertEquals(24, $value);
    }
}
