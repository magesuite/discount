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

        if ($product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            $prices = $this->getPricesForDynamicPriceType($product);
        } else {
            $prices = $this->getPricesForFixedPriceType($product);
        }

        $productPricesContainer
            ->setRegularPrice($prices['regular_price'])
            ->setFinalPrice($prices['final_price']);

        return $productPricesContainer;
    }

    /**
     * For the bundle with dynamic price type, discount is a difference between regular price and final price
     *
     * @param $product
     * @return array
     */
    protected function getPricesForDynamicPriceType($product)
    {
        if ($product->hasData('final_price') && $product->hasData('price')) {
            $regularPrice = $product->getData('price');
            $finalPrice = $finalPrice ?? $product->getData('final_price');
        } else {
            $regularPrice = $product->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE)->getAmount()->getValue();
            $finalPrice = $finalPrice ?? $product->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)->getAmount()->getValue();
        }

        return [
            'regular_price' => $regularPrice,
            'final_price' => $finalPrice
        ];
    }

    /**
     * For the bundle with fixed price type, discount is a percentage value
     *
     * @param $product
     * @return array
     */
    protected function getPricesForFixedPriceType($product)
    {
        return [
            'regular_price' => 100,
            'final_price' => 100 - $product->getData('special_price')
        ];
    }
}
