<?php

namespace App\Services\Organizations\Exceptions;

use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

final class OrganizationSyncThrottledException extends RuntimeException implements ShouldntReport
{
    public function __construct(
        public readonly int $retryAfterSeconds,
    ) {
        parent::__construct(
            'Синхронизация этой организации уже была запущена. Повторите запрос позже.',
        );
    }
}
