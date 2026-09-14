<?php

namespace App\Services\Organizations\DTO;

use App\Enums\OrganizationSource;

final readonly class ResolvedOrganization
{
    public function __construct(
        public OrganizationSource $source,
        public string $externalId,
    ) {}
}
