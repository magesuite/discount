<?php

namespace MageSuite\Discount\Helper;

class Configuration
{
    const MINIMAL_SALE_PERCENTAGE_PATH = 'catalog/frontend/minimal_sale_percentage';

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
}
