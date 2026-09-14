<?php

namespace App\Services\YandexMaps\Exceptions;

use RuntimeException;
use Throwable;

final class UnexpectedYandexMapsResponseException extends RuntimeException
{
    public static function create(?Throwable $previous = null): self
    {
        return new self('Яндекс Карты выдали неожиданный ответ.', previous: $previous);
    }
}
