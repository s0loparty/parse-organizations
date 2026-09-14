<?php

namespace App\Services\Organizations\DTO;

use Carbon\CarbonImmutable;

final readonly class FetchedReview
{
    public function __construct(
        public string $externalId,
        public ?string $authorName,
        public ?string $authorAvatarUrl,
        public int $rating,
        public ?string $text,
        public int $likesCount,
        public int $dislikesCount,
        public CarbonImmutable $sourceUpdatedAt,
    ) {}
}
