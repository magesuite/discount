<?php

declare(strict_types=1);

namespace MageSuite\Discount\Helper;

class Configuration
{
    public const XML_PATH_CATALOG_FRONTEND_MINIMAL_SALE_PERCENTAGE = 'catalog/frontend/minimal_sale_percentage';
    public const XML_PATH_CATALOG_FRONTEND_ROUNDING_SALE_PERCENTAGE = 'catalog/frontend/rounding_sale_percentage';
    public const XML_PATH_CATALOG_FRONTEND_IS_SPECIAL_PRICE_RESOLVER_ENABLED = 'catalog/frontend/is_special_price_resolver_enabled';
    public const XML_PATH_CATALOG_FRONTEND_SALE_PERCENTAGE_CALCULATION_TYPE = 'catalog/frontend/sale_percentage_calculation_type';
    public const XML_PATH_CATALOG_FRONTEND_SHOW_BIGGEST_DISCOUNT_FROM_CHILDREN_OF_GROUPED_PRODUCT = 'catalog/frontend/show_biggest_discount_from_children_of_grouped_product';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface)
    {
        $this->scopeConfig = $scopeConfigInterface;
    }

    public function getMinimalSalePercentage(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_CATALOG_FRONTEND_MINIMAL_SALE_PERCENTAGE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getRoundingSalePercentage(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_CATALOG_FRONTEND_ROUNDING_SALE_PERCENTAGE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isSpecialPriceResolverEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CATALOG_FRONTEND_IS_SPECIAL_PRICE_RESOLVER_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSalePercentageCalculationType(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CATALOG_FRONTEND_SALE_PERCENTAGE_CALCULATION_TYPE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function showBiggestDiscountFromChildrenOfGroupedProduct(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CATALOG_FRONTEND_SHOW_BIGGEST_DISCOUNT_FROM_CHILDREN_OF_GROUPED_PRODUCT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
