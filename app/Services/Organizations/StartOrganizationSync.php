<?php

namespace App\Services\Organizations;

use App\Enums\OrganizationStatus;
use App\Enums\OrganizationSyncStatus;
use App\Jobs\Organizations\SyncOrganization;
use App\Models\Organization;
use App\Services\Organizations\DTO\ResolvedOrganization;
use App\Services\Organizations\Exceptions\OrganizationSyncThrottledException;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\DB;

final class StartOrganizationSync
{
    private const int SYNC_COOLDOWN_SECONDS = 300;

    public function __construct(
        private RateLimiter $rateLimiter,
    ) {}

    public function handle(
        ResolvedOrganization $resolvedOrganization,
    ): Organization {
        return DB::transaction(function () use ($resolvedOrganization): Organization {
            $organization = Organization::query()->firstOrCreate(
                [
                    'source' => $resolvedOrganization->source,
                    'external_id' => $resolvedOrganization->externalId,
                ],
                [
                    'status' => OrganizationStatus::PENDING,
                ],
            );

            $organization = Organization::query()
                ->lockForUpdate()
                ->findOrFail($organization->id);

            $rateLimitKey = $this->rateLimitKey($resolvedOrganization);

            if ($this->rateLimiter->tooManyAttempts($rateLimitKey, 1)) {
                throw new OrganizationSyncThrottledException(
                    retryAfterSeconds: max(1, $this->rateLimiter->availableIn($rateLimitKey)),
                );
            }

            $hasActiveAttempt = $organization->syncAttempts()
                ->whereIn('status', [
                    OrganizationSyncStatus::PENDING,
                    OrganizationSyncStatus::PROCESSING,
                ])
                ->exists();

            if ($hasActiveAttempt) {
                return $organization;
            }

            $syncAttempt = $organization->syncAttempts()->create([
                'status' => OrganizationSyncStatus::PENDING,
            ]);

            SyncOrganization::dispatch(
                $organization->id,
                $syncAttempt->id,
            )->afterCommit();

            $this->rateLimiter->hit($rateLimitKey, self::SYNC_COOLDOWN_SECONDS);

            return $organization;
        });
    }

    private function rateLimitKey(ResolvedOrganization $resolvedOrganization): string
    {
        return implode(':', [
            'organization-sync',
            $resolvedOrganization->source->value,
            $resolvedOrganization->externalId,
        ]);
    }
}
