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
        /** @var \MageSuite\Discount\Api\Data\ProductPricesInterface $productPrices */
        $productPrices = $this->productPriceResolver->getPrices($product, $finalPrice);

        if (!$productPrices->getRegularPrice()) {
            $productPrices->setRegularPrice(
                $product->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE)->getAmount()->getValue()
            );
        }

        if (!$productPrices->getFinalPrice()) {
            $productPrices->setFinalPrice(
                $product->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)->getAmount()->getValue()
            );
        }

        if (!$productPrices->getRegularPrice() || !$productPrices->getFinalPrice() || $productPrices->getRegularPrice() <= $productPrices->getFinalPrice()) {
            return 0;
        }

        return (int)$this->calculateDiscountPercent($productPrices->getRegularPrice(), $productPrices->getFinalPrice());
    }

    protected function calculateDiscountPercent($regularPrice, $finalPrice)
    {
        return round((($regularPrice - $finalPrice) / $regularPrice) * 100, 0);
    }
}
