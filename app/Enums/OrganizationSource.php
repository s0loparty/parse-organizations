<?php

namespace App\Enums;

enum OrganizationSource: string
{
    case YANDEX = 'yandex';
    case TWO_GIS = '2gis';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
