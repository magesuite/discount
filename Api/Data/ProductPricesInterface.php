<?php

namespace MageSuite\Discount\Api\Data;

interface ProductPricesInterface
{
    /**
     * @return float
     */
    public function getRegularPrice();

    /**
     * @param string|float $regularPrice
     * @return $this
     */
    public function setRegularPrice($regularPrice);

    /**
     * @return float
     */
    public function getFinalPrice();

    /**
     * @param string|float $finalPrice
     * @return $this
     */
    public function setFinalPrice($finalPrice);
}
