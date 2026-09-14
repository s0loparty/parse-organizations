<?php

namespace App\Services\YandexMaps;

use App\Services\Organizations\Contracts\OrganizationDataProvider;
use App\Services\Organizations\DTO\FetchedOrganizationData;
use App\Services\Organizations\Exceptions\OrganizationNotFoundException;
use App\Services\YandexMaps\Exceptions\UnexpectedYandexMapsResponseException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Uri;
use JsonException;
use Throwable;

final class YandexMapsClient implements OrganizationDataProvider
{
    private const string ORGANIZATION_URL = 'https://yandex.ru/maps/org/%s/';

    public function fetch(string $externalId): FetchedOrganizationData
    {
        $response = Http::withHeaders([
            'Accept' => 'text/html,application/xhtml+xml',
            'Accept-Language' => 'ru-RU,ru;q=0.9',
        ])
            ->withUserAgent('Mozilla/5.0 (compatible; OrganizationSync/1.0)')
            ->connectTimeout(3)
            ->timeout(10)
            ->get(sprintf(self::ORGANIZATION_URL, $externalId));

        if ($response->notFound()) {
            throw OrganizationNotFoundException::forExternalId($externalId);
        }

        $response->throw();

        return $this->organizationDataFromResponse($response, $externalId);
    }

    public function isShortOrganizationUrl(string $url): bool
    {
        $uri = $this->yandexUri($url);

        return $uri !== null
            && (bool) preg_match('~^maps/-/[^/]+$~', $uri->path());
    }

    public function extractOrganizationId(string $url): ?string
    {
        $uri = $this->yandexUri($url);

        if ($uri === null) {
            return null;
        }

        $poiUri = $uri->query()->get('poi.uri');

        if (! is_string($poiUri)) {
            return null;
        }

        try {
            $organizationId = Uri::of($poiUri)->query()->get('oid');
        } catch (Throwable) {
            return null;
        }

        return is_string($organizationId) && ctype_digit($organizationId)
            ? $organizationId
            : null;
    }

    public function resolveShortOrganizationId(string $url): ?string
    {
        if (! $this->isShortOrganizationUrl($url)) {
            return null;
        }

        $response = Http::withoutRedirecting()
            ->connectTimeout(3)
            ->timeout(5)
            ->get($url);

        if (! $response->redirect()) {
            return null;
        }

        $location = $response->header('Location');

        if (! is_string($location) || $location === '') {
            return null;
        }

        $redirectUrl = $this->absoluteRedirectUrl($url, $location);

        return $redirectUrl === null
            ? null
            : $this->extractOrganizationId($redirectUrl);
    }

    private function yandexUri(string $url): ?Uri
    {
        try {
            $uri = Uri::of($url);
        } catch (Throwable) {
            return null;
        }

        if ($uri->scheme() !== 'https' || ! $this->isYandexHost($uri->host())) {
            return null;
        }

        return $uri;
    }

    private function absoluteRedirectUrl(string $sourceUrl, string $location): ?string
    {
        try {
            $locationUri = Uri::of($location);
        } catch (Throwable) {
            return null;
        }

        if ($locationUri->host() !== null) {
            return $location;
        }

        if (! str_starts_with($location, '/')) {
            return null;
        }

        $sourceUri = $this->yandexUri($sourceUrl);

        if ($sourceUri === null) {
            return null;
        }

        return $sourceUri->scheme().'://'.$sourceUri->host().$location;
    }

    private function isYandexHost(?string $host): bool
    {
        if ($host === null || $host === '') {
            return false;
        }

        return (bool) preg_match(
            '/^(?:[a-z0-9-]+\.)*yandex\.[a-z]{2,}$/',
            mb_strtolower($host),
        );
    }

    private function organizationDataFromResponse(
        Response $response,
        string $externalId,
    ): FetchedOrganizationData {
        if (! preg_match(
            '~<script\b[^>]*class="state-view"[^>]*>(.*?)</script>~is',
            $response->body(),
            $matches,
        )) {
            throw UnexpectedYandexMapsResponseException::create();
        }

        try {
            $state = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw UnexpectedYandexMapsResponseException::create($exception);
        }

        $organization = Arr::get($state, 'stack.0.results.items.0');

        if (! is_array($organization)
            || (string) Arr::get($organization, 'id') !== $externalId) {
            throw OrganizationNotFoundException::forExternalId($externalId);
        }

        $name = Arr::get($organization, 'title');
        $rating = Arr::get($organization, 'ratingData.ratingValue');
        $ratingsCount = Arr::get($organization, 'ratingData.ratingCount', 0);
        $reviewsCount = Arr::get($organization, 'ratingData.reviewCount', 0);

        if (! is_string($name)
            || $name === ''
            || (! is_int($rating) && ! is_float($rating) && $rating !== null)
            || ! is_int($ratingsCount)
            || ! is_int($reviewsCount)) {
            throw UnexpectedYandexMapsResponseException::create();
        }

        return new FetchedOrganizationData(
            name: $name,
            rating: $rating === null ? null : (string) $rating,
            ratingsCount: $ratingsCount,
            reviewsCount: $reviewsCount,
        );
    }
}
