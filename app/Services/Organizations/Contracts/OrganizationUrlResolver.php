<?php

namespace App\Services\Organizations\Contracts;

use App\Services\Organizations\DTO\ResolvedOrganization;

interface OrganizationUrlResolver
{
    public function supports(string $url): bool;

    public function resolve(string $url): ResolvedOrganization;
}
