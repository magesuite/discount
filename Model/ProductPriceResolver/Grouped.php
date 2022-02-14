<?php

namespace MageSuite\Discount\Model\ProductPriceResolver;

class Grouped extends ProductPriceResolver implements ProductPriceResolverInterface
{
    public function isApplicable($productTypeId)
    {
        return $productTypeId == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE;
    }

    public function getPrices($product, $finalPrice)
    {
        $productPricesContainer = $this->getProductPricesContainer();
        $minProduct = $product->getPriceInfo()->getPrice(\Magento\GroupedProduct\Pricing\Price\FinalPrice::PRICE_CODE)->getMinProduct();

        if (!$finalPrice && $minProduct instanceof \Magento\Catalog\Api\Data\ProductInterface) {
            $finalPrice = $minProduct->getFinalPrice();
            $productPricesContainer->setRegularPrice($minProduct->getPrice());
        }

        $productPricesContainer->setFinalPrice($finalPrice);

        return $productPricesContainer;
    }
}
