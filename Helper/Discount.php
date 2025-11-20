<?php

namespace MageSuite\Discount\Helper;

class Discount extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected \MageSuite\Discount\Helper\Configuration $configuration;
    protected \MageSuite\Discount\Model\Command\GetMaxPriceForConfigurableProduct $getMaxPriceForConfigurableProduct;
    protected \MageSuite\Discount\Model\Command\GetSalePercentage $getSalePercentage;
    protected \MageSuite\Discount\Model\Container\ProductPriceData $container;
    protected \MageSuite\Discount\Model\AddChildrenWithPricesToLoadedItems $addChildrenWithPricesToLoadedItems;

    /**
     * Product Sku => salePercentage
     */
    protected array $cachedSalePercentage;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \MageSuite\Discount\Model\Command\GetSalePercentage $getSalePercentage,
        \MageSuite\Discount\Helper\Configuration $configuration,
        \MageSuite\Discount\Model\Command\GetMaxPriceForConfigurableProduct $getMaxPriceForConfigurableProduct,
        \MageSuite\Discount\Model\Container\ProductPriceData $container,
        \MageSuite\Discount\Model\AddChildrenWithPricesToLoadedItems $addChildrenWithPricesToLoadedItems
    ) {
        parent::__construct($context);

        $this->getSalePercentage = $getSalePercentage;
        $this->configuration = $configuration;
        $this->getMaxPriceForConfigurableProduct = $getMaxPriceForConfigurableProduct;
        $this->container = $container;
        $this->addChildrenWithPricesToLoadedItems = $addChildrenWithPricesToLoadedItems;
    }

    public function isOnSale(\Magento\Catalog\Api\Data\ProductInterface $product, ?float $finalPrice = null): bool
    {
        $salePercentage = $this->getCachedSalePercentage($product->getSku(), $finalPrice) ?? $this->getSalePercentage->execute($product, $finalPrice);

        if ($salePercentage !== null) {
            $this->setCachedSalePercentage($product->getSku(), $finalPrice, $salePercentage);
        }

        return $salePercentage > 0;
    }

    public function getSalePercentage(\Magento\Catalog\Api\Data\ProductInterface $product, ?float $finalPrice = null, bool $isOutOfStock = false): int
    {
        if (
            $product->getTypeId() === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
            && $this->configuration->getSalePercentageCalculationType() === \MageSuite\Discount\Model\Config\Source\CalculationType::CALCULATION_TYPE_BIGGEST_DIFFERENCE_BETWEEN_SAME_SIMPLE_SPEICAL_AND_REGULAR_PRICE
            && !$isOutOfStock
        ) {
            return $this->getBiggestConfigurationSalePercentage($product);
        }

        if (
            $product->getTypeId() === \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE
            && $this->configuration->showBiggestDiscountFromChildrenOfGroupedProduct()
            && !$isOutOfStock
        ) {
            return $this->getBiggestSalePercentageFromChildrenOfGroupedProduct($product, $finalPrice);
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

    public function getConfigurableDiscounts(\Magento\Catalog\Api\Data\ProductInterface $product): array
    {
        if ($product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
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
        if ($product->getData('origins_from_collection') instanceof \Magento\Eav\Model\Entity\Collection\AbstractCollection) {
            $this->addChildrenWithPricesToLoadedItems->execute($product->getData('origins_from_collection'));
        }

        $childrenProducts = $product->getChildrenWithPrices();

        if (!empty($childrenProducts)) {
            return $childrenProducts;
        }

        return $product->getTypeInstance()->getUsedProducts($product);
    }

    protected function getConfigurableChildProductDiscount(?float $maxConfigurablePrice, \Magento\Catalog\Api\Data\ProductInterface $childProduct): ?int
    {
        //ensure product has correct prices for configurable item
        $childProductPrice = $childProduct->getData('final_price') ?? $childProduct->getFinalPrice();
        if ($childProductPrice === null) {
            $childProductPrice = $childProduct->getPriceInfo()
                ->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)
                ->getAmount()
                ->getValue();
        }

        if ($this->configuration->getSalePercentageCalculationType() === \MageSuite\Discount\Model\Config\Source\CalculationType::CALCULATION_TYPE_CHEAPEST_SIMPLE_TO_MOST_EXPENSIVE_REGULAR) {
            $childProduct->setData('price', $maxConfigurablePrice);
        }

        $childProduct->setData('final_price', $childProductPrice);

        return $this->getSalePercentage($childProduct);
    }

    protected function getCachedSalePercentage(string $productSku, ?float $finalPrice): ?int
    {
        if (!isset($this->cachedSalePercentage[$productSku])) {
            return null;
        }

        if ($finalPrice) {
            return $this->cachedSalePercentage[$productSku][(string)$finalPrice] ?? null;
        }

        return $this->cachedSalePercentage[$productSku]['default'] ?? null;
    }

    protected function setCachedSalePercentage(string $productSku, ?float $finalPrice, int $salePercentage): void
    {
        if ($finalPrice) {
            $this->cachedSalePercentage[$productSku][(string)$finalPrice] = $salePercentage;
        } else {
            $this->cachedSalePercentage[$productSku]['default'] = $salePercentage;
        }
    }

    protected function getBiggestConfigurationSalePercentage(\Magento\Catalog\Api\Data\ProductInterface $product): int
    {
        $discounts = $this->getConfigurableDiscounts($product);

        if (!empty($discounts)) {
            return max($discounts);
        }

        return 0;
    }

    protected function getBiggestSalePercentageFromChildrenOfGroupedProduct(\Magento\Catalog\Api\Data\ProductInterface $product, ?float $finalPrice = null): int
    {
        $salePercentage = 0;
        $associatedProducts = $product->getTypeInstance()->getAssociatedProducts($product);

        foreach ($associatedProducts as $associatedProduct) {
            $childrenSalePercentage = $this->getCachedSalePercentage($associatedProduct->getSku(), $finalPrice) ?? $this->getSalePercentage->execute($associatedProduct, $finalPrice);
            if ($childrenSalePercentage > $salePercentage) {
                $salePercentage = $childrenSalePercentage;
            }
        }

        if ((int)$salePercentage >= $this->configuration->getMinimalSalePercentage()) {
            return $salePercentage;
        }

        return 0;
    }
}
