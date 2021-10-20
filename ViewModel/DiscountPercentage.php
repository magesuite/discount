<?php

namespace MageSuite\Discount\ViewModel;

class DiscountPercentage implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \MageSuite\Discount\Helper\Configuration
     */
    protected $configuration;

    public function __construct(\MageSuite\Discount\Helper\Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getDiscountPercentage($originalPrice, $finalPrice, $precision = 0)
    {
        $discountPercentage = (($originalPrice - $finalPrice) * 100) / $originalPrice;
        $discountPercentage = round($discountPercentage, $precision);

        if ($discountPercentage >= $this->configuration->getMinimalSalePercentage()) {
            return $discountPercentage;
        }

        return 0;
    }
}
