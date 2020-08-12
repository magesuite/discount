<?php

namespace MageSuite\Discount\Model\ProductPriceResolver;

class Bundle extends ProductPriceResolver implements ProductPriceResolverInterface
{
    public function isApplicable($productTypeId)
    {
        return $productTypeId == \Magento\Bundle\Model\Product\Type::TYPE_CODE;
    }

    public function getPrices($product, $finalPrice)
    {
        $productPricesContainer = $this->getProductPricesContainer();

        $productPricesContainer
            ->setRegularPrice(100)
            ->setFinalPrice(100 - $product->getData('special_price'));

        return $productPricesContainer;
    }
}
