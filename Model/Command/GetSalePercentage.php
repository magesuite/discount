<?php

namespace MageSuite\Discount\Model\Command;

class GetSalePercentage
{
    /**
     * @var \MageSuite\Discount\Api\ProductPriceResolverInterface
     */
    protected $productPriceResolver;

    public function __construct(\MageSuite\Discount\Api\ProductPriceResolverInterface $productPriceResolver)
    {
        $this->productPriceResolver = $productPriceResolver;
    }

    public function execute($product, $finalPrice)
    {
        $productPrices = $this->productPriceResolver->getPrices($product, $finalPrice);

        if (!$productPrices['regular_price']) {
            $productPrices['regular_price'] = $product->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE)->getAmount()->getValue();
        }

        if (!$productPrices['final_price']) {
            $productPrices['final_price'] = $product->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)->getAmount()->getValue();
        }

        if (!$productPrices['regular_price'] || !$productPrices['final_price'] || $productPrices['regular_price'] <= $productPrices['final_price']) {
            return 0;
        }

        return $this->calculateDiscountPercent($productPrices['regular_price'], $productPrices['final_price']);
    }

    protected function calculateDiscountPercent($regularPrice, $finalPrice)
    {
        return round((($regularPrice - $finalPrice) / $regularPrice) * 100, 0);
    }
}
