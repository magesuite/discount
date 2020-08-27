<?php

namespace MageSuite\Discount\Helper;

class Discount extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \MageSuite\Discount\Model\Command\GetSalePercentage
     */
    protected $getSalePercentage;

    /**
     * @var \MageSuite\Discount\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \MageSuite\Discount\Model\Command\GetMaxPriceForConfigurableProduct
     */
    protected $getMaxPriceForConfigurableProduct;

    /**
     * Product Sku => salePercentage
     * @var array
     */
    protected $cachedSalePercentage;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \MageSuite\Discount\Model\Command\GetSalePercentage $getSalePercentage,
        \MageSuite\Discount\Helper\Configuration $configuration,
        \MageSuite\Discount\Model\Command\GetMaxPriceForConfigurableProduct $getMaxPriceForConfigurableProduct
    ) {
        parent::__construct($context);

        $this->getSalePercentage = $getSalePercentage;
        $this->configuration = $configuration;
        $this->getMaxPriceForConfigurableProduct = $getMaxPriceForConfigurableProduct;
    }

    public function isOnSale($product, $finalPrice = null)
    {
        $salePercentage = $this->getCachedSalePercentage($product->getSku(), $finalPrice) ?? $this->getSalePercentage->execute($product, $finalPrice);

        if ($salePercentage !== null) {
            $this->setCachedSalePercentage($product->getSku(), $finalPrice, $salePercentage);
        }

        return $salePercentage > 0;
    }

    public function getSalePercentage($product, $finalPrice = null)
    {
        $salePercentage = $this->getCachedSalePercentage($product->getSku(), $finalPrice) ?? $this->getSalePercentage->execute($product, $finalPrice);

        if ($salePercentage !== null) {
            $this->setCachedSalePercentage($product->getSku(), $finalPrice, $salePercentage);
        }

        if ((int)$salePercentage >= $this->configuration->getMinimalSalePercentage()) {
            return $salePercentage;
        }

        return 0;
    }

    public function getConfigurableDiscounts($product)
    {
        if (!$product instanceof \Magento\Catalog\Api\Data\ProductInterface || $product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return [];
        }

        $childrenProducts = $product->getChildrenWithPrices();

        if (empty($childrenProducts)) {
            $childrenProducts = $product->getTypeInstance()->getUsedProducts($product);
        }

        $configurableDiscounts = [];
        $maxConfigurablePrice = $this->getMaxPriceForConfigurableProduct->execute($product);

        foreach ($childrenProducts as $childProduct) {
            $configurableDiscounts[$childProduct->getId()] = $this->getConfigurableChildProductDiscount($maxConfigurablePrice, $childProduct);
        }

        if (empty($configurableDiscounts)) {
            $configurableDiscounts[$product->getId()] = $this->getSalePercentage($product);
        }

        return $configurableDiscounts;
    }

    protected function getConfigurableChildProductDiscount($maxConfigurablePrice, $childProduct)
    {
        //ensure product has correct prices for configurable item
        $childProductPrice = $childProduct->getData('final_price') ?? $childProduct->getFinalPrice();

        $childProduct->setData('price', $maxConfigurablePrice);
        $childProduct->setData('final_price', $childProductPrice);

        return $this->getSalePercentage($childProduct);
    }

    protected function getCachedSalePercentage($productSku, $finalPrice)
    {
        if (!isset($this->cachedSalePercentage[$productSku])) {
            return null;
        }

        if ($finalPrice) {
            return $this->cachedSalePercentage[$productSku][$finalPrice] ?? null;
        }

        return $this->cachedSalePercentage[$productSku];
    }

    protected function setCachedSalePercentage($productSku, $finalPrice, $salePercentage)
    {
        if ($finalPrice) {
            $this->cachedSalePercentage[$productSku][$finalPrice] = $salePercentage;
        } else {
            $this->cachedSalePercentage[$productSku] = $salePercentage;
        }
    }
}
