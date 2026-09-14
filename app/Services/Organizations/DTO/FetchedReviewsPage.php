<?php

namespace App\Services\Organizations\DTO;

final readonly class FetchedReviewsPage
{
    /**
     * @param  list<FetchedReview>  $reviews
     */
    public function __construct(
        public array $reviews,
        public int $page,
        public int $limit,
        public int $totalPages,
        public int $totalReviews,
        public int $loadedReviewsCount,
        public int $reviewsRemained,
    ) {}
}
