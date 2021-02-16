<?php
namespace MageSuite\Discount\Model\Config\Source;

class CalculationType implements \Magento\Framework\Option\ArrayInterface
{
    const CALCULATION_TYPE_CHEAPEST_SIMPLE_TO_MOST_EXPENSIVE_REGULAR = 'cheapest_simple_special_to_most_expensive_regular';
    const CALCULATION_TYPE_BIGGEST_DIFFERENCE_BETWEEN_SAME_SIMPLE_SPEICAL_AND_REGULAR_PRICE = 'biggest_difference_between_same_simple_special_and_regular_price';

    public function toOptionArray()
    {
        return [
            ['value' => self::CALCULATION_TYPE_CHEAPEST_SIMPLE_TO_MOST_EXPENSIVE_REGULAR, 'label' => __('Cheapest simple product special price to most expensive regular price')],
            ['value' => self::CALCULATION_TYPE_BIGGEST_DIFFERENCE_BETWEEN_SAME_SIMPLE_SPEICAL_AND_REGULAR_PRICE, 'label' => __('Biggest difference between special and regular price within one simple product')],
        ];
    }
}
