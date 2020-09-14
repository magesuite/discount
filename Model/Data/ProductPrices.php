<?php

namespace MageSuite\Discount\Model\Data;

class ProductPrices extends \Magento\Framework\DataObject implements \MageSuite\Discount\Api\Data\ProductPricesInterface
{
    public function getRegularPrice()
    {
        return $this->getData('regular_price');
    }

    public function setRegularPrice($regularPrice)
    {
        $this->setData('regular_price', (float)$regularPrice);

        return $this;
    }

    public function getFinalPrice()
    {
        return $this->getData('final_price');
    }

    public function setFinalPrice($finalPrice)
    {
        $this->setData('final_price', (float)$finalPrice);

        return $this;
    }
}
