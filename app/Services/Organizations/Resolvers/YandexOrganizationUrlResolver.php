<?php

namespace App\Services\Organizations\Resolvers;

use App\Enums\OrganizationSource;
use App\Services\Organizations\Contracts\OrganizationUrlResolver;
use App\Services\Organizations\DTO\ResolvedOrganization;
use App\Services\Organizations\Exceptions\UnsupportedOrganizationUrlException;
use App\Services\YandexMaps\YandexMapsClient;

final readonly class YandexOrganizationUrlResolver implements OrganizationUrlResolver
{
    public function __construct(
        private YandexMapsClient $client,
    ) {}

    public function supports(string $url): bool
    {
        return $this->client->extractOrganizationId($url) !== null
            || $this->client->isShortOrganizationUrl($url);
    }

    public function resolve(string $url): ResolvedOrganization
    {
        $externalId = $this->client->extractOrganizationId($url)
            ?? $this->client->resolveShortOrganizationId($url);

        if ($externalId === null) {
            throw UnsupportedOrganizationUrlException::create();
        }

        return new ResolvedOrganization(
            source: OrganizationSource::YANDEX,
            externalId: $externalId,
        );
    }
}
