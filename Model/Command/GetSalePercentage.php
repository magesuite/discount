<?php

declare(strict_types=1);

namespace MageSuite\Discount\Model\Command;

class GetSalePercentage
{
    public function __construct(
        protected \MageSuite\Discount\Api\ProductPriceResolverInterface $productPriceResolver,
        protected \MageSuite\Discount\Helper\Configuration $configuration
    ){}

    public function execute(\Magento\Catalog\Api\Data\ProductInterface $product, ?float $finalPrice = null): int
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

        return $this->calculateDiscountPercent($productPrices->getRegularPrice(), $productPrices->getFinalPrice());
    }

    protected function calculateDiscountPercent(float $regularPrice, float $finalPrice): int
    {
        $roundingType = $this->configuration->getRoundingSalePercentage();

        $value = ($regularPrice * 100 - $finalPrice * 100) / $regularPrice;

        if ($roundingType) {
            return (int) round($value, 0, $roundingType);
        }

        return (int) $value;
    }
}
