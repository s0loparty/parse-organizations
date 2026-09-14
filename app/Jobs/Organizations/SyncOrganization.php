<?php

namespace App\Jobs\Organizations;

use App\Enums\OrganizationStatus;
use App\Enums\OrganizationSyncStatus;
use App\Models\Organization;
use App\Models\SyncAttempt;
use App\Services\Organizations\Exceptions\OrganizationNotFoundException;
use App\Services\Organizations\OrganizationDataProviderResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Throwable;

final class SyncOrganization implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public int $organizationId,
        public int $syncAttemptId,
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

        if ($syncAttempt->status === OrganizationSyncStatus::COMPLETED
            || ($syncAttempt->status === OrganizationSyncStatus::PROCESSING
                && $syncAttempt->reviews_total !== null)) {
            return;
        }

        $syncAttempt->update([
            'status' => OrganizationSyncStatus::PROCESSING,
            'error' => null,
        ]);

        try {
            $provider = $providerResolver->resolve($organization->source);
            $data = $provider->fetch($organization->external_id);
        } catch (OrganizationNotFoundException $exception) {
            DB::transaction(function () use ($organization, $syncAttempt, $exception): void {
                $organization->update([
                    'status' => OrganizationStatus::INVALID,
                ]);

                $syncAttempt->update([
                    'status' => OrganizationSyncStatus::FAILED,
                    'error' => $exception->getMessage(),
                ]);
            });

            return;
        }

        DB::transaction(function () use ($organization, $syncAttempt, $data): void {
            $organization->update([
                'name' => $data->name,
                'rating' => $data->rating,
                'ratings_count' => $data->ratingsCount,
                'reviews_count' => $data->reviewsCount,
                'status' => OrganizationStatus::VALID,
            ]);

            $hasReviews = $data->reviewsCount > 0;

            $syncAttempt->update([
                'status' => $hasReviews
                    ? OrganizationSyncStatus::PROCESSING
                    : OrganizationSyncStatus::COMPLETED,
                'error' => null,
                'reviews_processed' => 0,
                'reviews_total' => $data->reviewsCount,
                'next_reviews_page' => $hasReviews ? 1 : null,
            ]);

            if ($hasReviews) {
                SyncOrganizationReviewsPage::dispatch(
                    organizationId: $organization->id,
                    syncAttemptId: $syncAttempt->id,
                    page: 1,
                )->afterCommit();
            }
        });
    }

    public function failed(?Throwable $exception): void
    {
        SyncAttempt::query()
            ->whereKey($this->syncAttemptId)
            ->where('status', '!=', OrganizationSyncStatus::COMPLETED)
            ->update([
                'status' => OrganizationSyncStatus::FAILED,
                'error' => $exception?->getMessage() ?? 'Organization synchronization failed.',
            ]);
    }
}
