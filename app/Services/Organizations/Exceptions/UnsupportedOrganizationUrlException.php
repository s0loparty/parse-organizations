<?php

namespace App\Services\Organizations\Exceptions;

use Illuminate\Contracts\Debug\ShouldntReport;
use InvalidArgumentException;

final class UnsupportedOrganizationUrlException extends InvalidArgumentException implements ShouldntReport
{
    public static function create(): self
    {
        return new self('The organization URL is not supported.');
    }
}
