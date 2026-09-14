<?php

namespace App\Services\Organizations\Contracts;

use App\Services\Organizations\DTO\FetchedOrganizationData;

interface OrganizationDataProvider
{
    public function fetch(string $externalId): FetchedOrganizationData;
}
