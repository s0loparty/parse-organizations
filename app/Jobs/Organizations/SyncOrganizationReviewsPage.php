<?php

namespace App\Jobs\Organizations;

use App\Enums\OrganizationSyncStatus;
use App\Models\Organization;
use App\Models\Review;
use App\Models\SyncAttempt;
use App\Services\Organizations\DTO\FetchedReview;
use App\Services\Organizations\DTO\FetchedReviewsPage;
use App\Services\Organizations\OrganizationDataProviderResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Throwable;

final class SyncOrganizationReviewsPage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public int $organizationId,
        public int $syncAttemptId,
        public int $page,
    ) {}

    /** @return list<int> */
    public function backoff(): array
    {
        return [10, 60];
    }

    public function handle(OrganizationDataProviderResolver $providerResolver): void
    {
        $organization = Organization::query()->findOrFail($this->organizationId);
        $syncAttempt = SyncAttempt::query()
            ->whereBelongsTo($organization)
            ->findOrFail($this->syncAttemptId);

        if (! $this->shouldProcess($syncAttempt)) {
            return;
        }

        $provider = $providerResolver->resolve($organization->source);
        $reviewsPage = $provider->fetchReviewsPage($organization->external_id, $this->page);

        DB::transaction(function () use ($organization, $reviewsPage): void {
            $syncAttempt = SyncAttempt::query()
                ->whereBelongsTo($organization)
                ->lockForUpdate()
                ->findOrFail($this->syncAttemptId);

            if (! $this->shouldProcess($syncAttempt)) {
                return;
            }

            $this->upsertReviews($reviewsPage);

            $hasNextPage = $reviewsPage->page < $reviewsPage->totalPages
                || $reviewsPage->reviewsRemained > 0;
            $processedReviews = min(
                $reviewsPage->totalReviews,
                $reviewsPage->loadedReviewsCount,
            );

            $organization->update([
                'reviews_count' => $reviewsPage->totalReviews,
            ]);

            $syncAttempt->update([
                'status' => $hasNextPage
                    ? OrganizationSyncStatus::PROCESSING
                    : OrganizationSyncStatus::COMPLETED,
                'error' => null,
                'reviews_processed' => $processedReviews,
                'reviews_total' => $reviewsPage->totalReviews,
                'next_reviews_page' => $hasNextPage ? $reviewsPage->page + 1 : null,
            ]);

            if ($hasNextPage) {
                self::dispatch(
                    organizationId: $this->organizationId,
                    syncAttemptId: $this->syncAttemptId,
                    page: $reviewsPage->page + 1,
                )->delay(now()->addSeconds(2))->afterCommit();
            }
        });
    }

    public function failed(?Throwable $exception): void
    {
        SyncAttempt::query()
            ->whereKey($this->syncAttemptId)
            ->where('status', OrganizationSyncStatus::PROCESSING)
            ->where('next_reviews_page', $this->page)
            ->update([
                'status' => OrganizationSyncStatus::FAILED,
                'error' => $exception?->getMessage() ?? 'Organization reviews synchronization failed.',
            ]);
    }

    private function shouldProcess(SyncAttempt $syncAttempt): bool
    {
        return $syncAttempt->status === OrganizationSyncStatus::PROCESSING
            && $syncAttempt->next_reviews_page === $this->page;
    }

    private function upsertReviews(FetchedReviewsPage $reviewsPage): void
    {
        if ($reviewsPage->reviews === []) {
            return;
        }

        Review::query()->upsert(
            values: array_map(
                fn (FetchedReview $review): array => [
                    'organization_id' => $this->organizationId,
                    'external_id' => $review->externalId,
                    'author_name' => $review->authorName,
                    'author_avatar_url' => $review->authorAvatarUrl,
                    'rating' => $review->rating,
                    'text' => $review->text,
                    'likes_count' => $review->likesCount,
                    'dislikes_count' => $review->dislikesCount,
                    'source_updated_at' => $review->sourceUpdatedAt,
                ],
                $reviewsPage->reviews,
            ),
            uniqueBy: ['organization_id', 'external_id'],
            update: [
                'author_name',
                'author_avatar_url',
                'rating',
                'text',
                'likes_count',
                'dislikes_count',
                'source_updated_at',
            ],
        );
    }
}
