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
     * Product Sku => salePercentage
     * @var array
     */
    protected $cachedSalePercentage;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \MageSuite\Discount\Model\Command\GetSalePercentage $getSalePercentage,
        \MageSuite\Discount\Helper\Configuration $configuration
    ) {
        parent::__construct($context);

        $this->getSalePercentage = $getSalePercentage;
        $this->configuration = $configuration;
    }

    public function isOnSale($product, $finalPrice = null)
    {
        $salePercentage = $this->getCachedSalePercentage($product->getSku()) ?? $this->getSalePercentage->execute($product, $finalPrice);

        if ($salePercentage !== null) {
            $this->setCachedSalePercentage($product->getSku(), $salePercentage);
        }

        return $salePercentage > 0;
    }

    public function getSalePercentage($product, $finalPrice = null)
    {
        $salePercentage = $this->getCachedSalePercentage($product->getSku()) ?? $this->getSalePercentage->execute($product, $finalPrice);

        if ($salePercentage !== null) {
            $this->setCachedSalePercentage($product->getSku(), $salePercentage);
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
        $maxConfigurablePrice = $product->getPriceInfo()->getPrice(\Magento\ConfigurableProduct\Pricing\Price\ConfigurableRegularPrice::PRICE_CODE)->getMaxRegularAmount()->getValue();

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

    protected function getCachedSalePercentage($productSku)
    {
        if (!isset($this->cachedSalePercentage[$productSku])) {
            return null;
        }

        return $this->cachedSalePercentage[$productSku];
    }

    protected function setCachedSalePercentage($productSku, $salePercentage)
    {
        $this->cachedSalePercentage[$productSku] = $salePercentage;
    }
}
