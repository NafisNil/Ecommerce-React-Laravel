<?php

namespace App\Enums;

enum CouponTypeEnum: string
{
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';

    public static function labels(): array
    {
        return [
            self::PERCENTAGE->value => 'Percentage',
            self::FIXED->value => 'Fixed amount',
        ];
    }
}
