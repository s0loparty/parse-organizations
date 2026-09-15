<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StoreOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\ReviewResource;
use App\Models\Organization;
use App\Services\Organizations\Exceptions\OrganizationSyncThrottledException;
use App\Services\Organizations\Resolvers\YandexOrganizationUrlResolver;
use App\Services\Organizations\StartOrganizationSync;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrganizationController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $organizations = Organization::latest()->get();

        return OrganizationResource::collection($organizations);
    }

    public function store(
        StoreOrganizationRequest $request,
        YandexOrganizationUrlResolver $resolver,
        StartOrganizationSync $startOrganizationSync,
    ): OrganizationResource|JsonResponse {
        $resolvedOrganization = $resolver->resolve(
            $request->string('url')->toString(),
        );

        try {
            $organization = $startOrganizationSync->handle(
                $resolvedOrganization,
            );
        } catch (OrganizationSyncThrottledException $exception) {
            return response()->json(
                data: [
                    'message' => $exception->getMessage(),
                    'retry_after' => $exception->retryAfterSeconds,
                ],
                status: 429,
                headers: [
                    'Retry-After' => (string) $exception->retryAfterSeconds,
                ],
            );
        }

        return new OrganizationResource($organization);
    }

    public function show(Organization $organization): OrganizationResource
    {
        return new OrganizationResource($organization);
    }

    public function reviews(Organization $organization): AnonymousResourceCollection
    {
        $reviews = $organization->reviews()
            ->latest('source_updated_at')
            ->paginate(50);

        return ReviewResource::collection($reviews);
    }
}
