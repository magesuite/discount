<?php

namespace MageSuite\Discount\Plugin\Catalog\Pricing\Render\FinalPriceBox;

class OptimizeHasSpecialPriceMethod
{
    /**
     * @var \MageSuite\Discount\Helper\Configuration
     */
    protected $configuration;

    public function __construct(\MageSuite\Discount\Helper\Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function aroundHasSpecialPrice(\Magento\Catalog\Pricing\Render\FinalPriceBox $subject, \Closure $proceed)
    {
        $product = $subject->getSaleableItem();

        if ($this->configuration->isSpecialPriceResolverEnabled() && !$product->getSpecialPrice()) {
            return $proceed();
        }

        // phpcs:ignore
        if ($product->hasData('price') &&
            $product->hasData('final_price') &&
            (float)$product->getData('price') &&
            (float)$product->getData('final_price')
        ) {
            return $product->getData('final_price') < $product->getData('price');
        }

        return $proceed();
    }
}
