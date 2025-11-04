<?php

declare(strict_types=1);

namespace MageSuite\Discount\Model\Config\Source;

class RoundingType implements \Magento\Framework\Data\OptionSourceInterface
{
    public const ROUND_DOWN = 0;

    public function toOptionArray(): array
    {
        return [
            ['value' => self::ROUND_DOWN, 'label' => __('Rounds num towards zero, making 1.5 into 1 and 2.99 into 2.')],
            ['value' => PHP_ROUND_HALF_UP, 'label' => __('Rounds num away from zero when it is half way there, making 1.5 into 2 and -1.5 into -2.')],
            ['value' => PHP_ROUND_HALF_DOWN, 'label' => __('Rounds num towards zero when it is half way there, making 1.5 into 1 and -1.5 into -1.')]
        ];
    }
}
