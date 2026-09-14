<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StoreOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Services\Organizations\Exceptions\OrganizationSyncThrottledException;
use App\Services\Organizations\Resolvers\YandexOrganizationUrlResolver;
use App\Services\Organizations\StartOrganizationSync;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
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

    public function show(Organization $organization)
    {
        //
    }

    public function reviews(Request $request, Organization $organization)
    {
        //
    }
}
