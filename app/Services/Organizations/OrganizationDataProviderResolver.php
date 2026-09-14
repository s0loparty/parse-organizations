<?php

namespace App\Services\Organizations;

use App\Enums\OrganizationSource;
use App\Services\Organizations\Contracts\OrganizationDataProvider;
use App\Services\YandexMaps\YandexMapsClient;
use LogicException;

final class OrganizationDataProviderResolver
{
    public function __construct(
        private YandexMapsClient $yandexMapsClient,
    ) {}

    public function resolve(OrganizationSource $source): OrganizationDataProvider
    {
        return match ($source) {
            OrganizationSource::YANDEX => $this->yandexMapsClient,
            default => throw new LogicException(
                "Organization data provider for [{$source->value}] is not implemented.",
            ),
        };
    }
}
