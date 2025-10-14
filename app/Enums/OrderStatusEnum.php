<?php

namespace App\Enums;

enum OrderStatusEnum :string
{
    //
    case Draft = 'draft';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    case Shipped = 'shipped';
    case Delivered = 'delivered';

    public static function labels(): array
    {
        return [
            self::Draft->value => 'Draft',
            self::Paid->value => 'Paid',
            self::Cancelled->value => 'Cancelled',
            self::Refunded->value => 'Refunded',
            self::Shipped->value => 'Shipped',
            self::Delivered->value => 'Delivered',
        ];
    }
}
