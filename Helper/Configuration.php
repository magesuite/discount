<?php

namespace MageSuite\Discount\Helper;

class Configuration
{
    const MINIMAL_SALE_PERCENTAGE_PATH = 'catalog/frontend/minimal_sale_percentage';
    const SPECIAL_PRICE_RESOLVER_PATH = 'catalog/frontend/is_special_price_resolver_enabled';
    const SALE_PERCENTAGE_CALCULATION_TYPE = 'catalog/frontend/sale_percentage_calculation_type';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface)
    {
        $this->scopeConfig = $scopeConfigInterface;
    }

    public function getMinimalSalePercentage()
    {
        return $this->scopeConfig->getValue(self::MINIMAL_SALE_PERCENTAGE_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isSpecialPriceResolverEnabled()
    {
        return $this->scopeConfig->getValue(self::SPECIAL_PRICE_RESOLVER_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSalePercentageCalculationType()
    {
        return $this->scopeConfig->getValue(self::SALE_PERCENTAGE_CALCULATION_TYPE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
