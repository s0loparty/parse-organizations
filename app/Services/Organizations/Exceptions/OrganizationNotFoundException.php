<?php

namespace App\Services\Organizations\Exceptions;

use RuntimeException;

final class OrganizationNotFoundException extends RuntimeException
{
    public static function forExternalId(string $externalId): self
    {
        return new self("Организация [{$externalId}] не найдена.");
    }
}
