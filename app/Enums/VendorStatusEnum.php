<?php

namespace App\Enums;

enum VendorStatusEnum : string
{
    case Approved = 'approved';
    case Pending = 'pending';
    case Suspended = 'suspended';

    public static function labels(): array
    {
        return [
            self::Approved->value => 'Approved',
            self::Pending->value => 'Pending',
            self::Suspended->value => 'Suspended',
        ];
    }

    public static function colors(): array
    {
        return [
            self::Approved->value => 'green',
            self::Pending->value => 'yellow',
            self::Suspended->value => 'red',
        ];
    }
}
