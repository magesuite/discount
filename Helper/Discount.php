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
        $discount = $this->getSalePercentage->execute($product, $finalPrice);

        return $discount > 0;
    }

    public function getSalePercentage($product, $finalPrice = null)
    {
        $discount = $this->getSalePercentage->execute($product, $finalPrice);

        if ((int)$discount >= $this->configuration->getMinimalSalePercentage()) {
            return $discount;
        }

        return 0;
    }

    public function getConfigurableDiscounts($product)
    {
        if (!$product instanceof \Magento\Catalog\Api\Data\ProductInterface || $product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return [];
        }

        $configurableDiscounts = [];

        $simpleProducts = $product->getTypeInstance()->getUsedProducts($product);

        foreach ($simpleProducts as $simpleProduct) {
            $configurableDiscounts[$simpleProduct->getId()] = $this->getSalePercentage($simpleProduct);
        }

        if (empty($configurableDiscounts)) {
            $configurableDiscounts[$product->getId()] = $this->getSalePercentage($product);
        }

        return $configurableDiscounts;
    }
}
