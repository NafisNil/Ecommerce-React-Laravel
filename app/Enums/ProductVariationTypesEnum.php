<?php

namespace App\Enums;

enum ProductVariationTypesEnum:string
{
    //
    case Select = 'select';
    case Radio = 'radio';
    case Image = 'image';

    public static function labels(): array
    {
        return [
            self::Select->value => 'Select',
            self::Radio->value => 'Radio',
            self::Image->value => 'Image',
        ];
    }
}
