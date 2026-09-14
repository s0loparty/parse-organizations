<?php

namespace App\Services\Organizations\DTO;

final readonly class FetchedOrganizationData
{
    public function __construct(
        public string $name,
        public ?string $rating,
        public int $ratingsCount,
        public int $reviewsCount,
    ) {}
}
