<?php

namespace App\Enums;

enum OrganizationStatus: string
{
    case PENDING = 'pending';
    case VALID = 'valid';
    case INVALID = 'invalid';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
