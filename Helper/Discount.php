<?php

namespace MageSuite\Discount\Helper;

class Discount extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected \MageSuite\Discount\Helper\Configuration $configuration;
    protected \MageSuite\Discount\Model\Command\GetMaxPriceForConfigurableProduct $getMaxPriceForConfigurableProduct;
    protected \MageSuite\Discount\Model\Command\GetSalePercentage $getSalePercentage;
    protected \MageSuite\Discount\Model\Container\ProductPriceData $container;

    /**
     * Product Sku => salePercentage
     */
    protected array $cachedSalePercentage;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \MageSuite\Discount\Model\Command\GetSalePercentage $getSalePercentage,
        \MageSuite\Discount\Helper\Configuration $configuration,
        \MageSuite\Discount\Model\Command\GetMaxPriceForConfigurableProduct $getMaxPriceForConfigurableProduct,
        \MageSuite\Discount\Model\Container\ProductPriceData $container
    ) {
        parent::__construct($context);

        $this->getSalePercentage = $getSalePercentage;
        $this->configuration = $configuration;
        $this->getMaxPriceForConfigurableProduct = $getMaxPriceForConfigurableProduct;
        $this->container = $container;
    }

    public function isOnSale($product, $finalPrice = null): bool
    {
        $salePercentage = $this->getCachedSalePercentage($product->getSku(), $finalPrice) ?? $this->getSalePercentage->execute($product, $finalPrice);

        if ($salePercentage !== null) {
            $this->setCachedSalePercentage($product->getSku(), $finalPrice, $salePercentage);
        }

        return $salePercentage > 0;
    }

    public function getSalePercentage($product, $finalPrice = null, $isOutOfStock = false)
    {
        if (
            $product->getTypeId() === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE &&
            $this->configuration->getSalePercentageCalculationType() === \MageSuite\Discount\Model\Config\Source\CalculationType::CALCULATION_TYPE_BIGGEST_DIFFERENCE_BETWEEN_SAME_SIMPLE_SPEICAL_AND_REGULAR_PRICE &&
            !$isOutOfStock
        ) {
            return $this->getBiggestConfigurationSalePercentage($product);
        }

        $salePercentage = $this->getCachedSalePercentage($product->getSku(), $finalPrice) ?? $this->getSalePercentage->execute($product, $finalPrice);

        if ($salePercentage !== null) {
            $this->setCachedSalePercentage($product->getSku(), $finalPrice, $salePercentage);
        }

        if ((int)$salePercentage >= $this->configuration->getMinimalSalePercentage()) {
            return $salePercentage;
        }

        return 0;
    }

    public function getConfigurableDiscounts($product): array
    {
        if (!$product instanceof \Magento\Catalog\Api\Data\ProductInterface || $product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return [];
        }

        $childrenProducts = $this->getChildrenProducts($product);

        $productIds = array_map(function ($product) {
            return (int)$product->getId();
        }, $childrenProducts);

        $this->container->initProducts($productIds);

        $configurableDiscounts = [];
        $maxConfigurablePrice = $this->getMaxPriceForConfigurableProduct->execute($product);

        foreach ($childrenProducts as $childProduct) {
            $configurableDiscounts[$childProduct->getId()] = $this->getConfigurableChildProductDiscount($maxConfigurablePrice, $childProduct);
        }

        if (empty($configurableDiscounts)) {
            $configurableDiscounts[$product->getId()] = $this->getSalePercentage($product, null, true);
        }

        return $configurableDiscounts;
    }

    protected function getChildrenProducts(\Magento\Catalog\Api\Data\ProductInterface $product): array
    {
        $childrenProducts = $product->getChildrenWithPrices();

        if (!empty($childrenProducts)) {
            return $childrenProducts;
        }

        return $product->getTypeInstance()->getUsedProducts($product);
    }

    protected function getConfigurableChildProductDiscount($maxConfigurablePrice, $childProduct)
    {
        //ensure product has correct prices for configurable item
        $childProductPrice = $childProduct->getData('final_price') ?? $childProduct->getFinalPrice();
        if ($this->configuration->getSalePercentageCalculationType() === \MageSuite\Discount\Model\Config\Source\CalculationType::CALCULATION_TYPE_CHEAPEST_SIMPLE_TO_MOST_EXPENSIVE_REGULAR) {
            $childProduct->setData('price', $maxConfigurablePrice);
        }
        $childProduct->setData('final_price', $childProductPrice);

        return $this->getSalePercentage($childProduct);
    }

    protected function getCachedSalePercentage($productSku, $finalPrice)
    {
        if (!isset($this->cachedSalePercentage[$productSku])) {
            return null;
        }

        if ($finalPrice) {
            return $this->cachedSalePercentage[$productSku][(string)$finalPrice] ?? null;
        }

        return $this->cachedSalePercentage[$productSku]['default'] ?? null;
    }

    protected function setCachedSalePercentage($productSku, $finalPrice, $salePercentage)
    {
        if ($finalPrice) {
            $this->cachedSalePercentage[$productSku][(string)$finalPrice] = $salePercentage;
        } else {
            $this->cachedSalePercentage[$productSku]['default'] = $salePercentage;
        }
    }

    protected function getBiggestConfigurationSalePercentage($product)
    {
        $discounts = $this->getConfigurableDiscounts($product);
        if (!empty($discounts)) {
            return max($discounts);
        }

        return 0;
    }
}
