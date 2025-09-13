<?php

namespace App\Enums;

enum ProductStatusEnum : string
{
    //
    case DRAFT = 'draft';
    case PUBLISHED = 'published';

    public static function labels(): array
    {
        return [
            self::DRAFT->value => 'Draft',
            self::PUBLISHED->value => 'Published',
        ];
    }

    public static function colors(): array
    {
        return [
            self::DRAFT->value => 'gray',
            self::PUBLISHED->value => 'green',
        ];
    }
}
