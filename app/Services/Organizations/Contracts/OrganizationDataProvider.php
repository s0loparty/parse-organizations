<?php

namespace App\Services\Organizations\Contracts;

use App\Services\Organizations\DTO\FetchedOrganizationData;
use App\Services\Organizations\DTO\FetchedReviewsPage;

interface OrganizationDataProvider
{
    public function fetch(string $externalId): FetchedOrganizationData;

    public function fetchReviewsPage(string $externalId, int $page): FetchedReviewsPage;
}
