<?php

namespace App\Services\YandexMaps;

use App\Services\Organizations\Contracts\OrganizationDataProvider;
use App\Services\Organizations\DTO\FetchedOrganizationData;
use App\Services\Organizations\DTO\FetchedReview;
use App\Services\Organizations\DTO\FetchedReviewsPage;
use App\Services\Organizations\Exceptions\OrganizationNotFoundException;
use App\Services\YandexMaps\Exceptions\UnexpectedYandexMapsResponseException;
use Carbon\CarbonImmutable;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Uri;
use InvalidArgumentException;
use JsonException;
use Throwable;

final class YandexMapsClient implements OrganizationDataProvider
{
    private const string ORGANIZATION_URL = 'https://yandex.ru/maps/org/%s/';

    private const string ORGANIZATION_REVIEWS_URL = 'https://yandex.ru/maps/org/%s/reviews/';

    private const string REVIEWS_API_URL = 'https://yandex.ru/maps/api/business/fetchReviews';

    private const string USER_AGENT = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 Chrome/140.0.0.0 Safari/537.36';

    public function fetch(string $externalId): FetchedOrganizationData
    {
        $response = $this->get(sprintf(self::ORGANIZATION_URL, $externalId));
        $this->ensureSuccessfulResponse($response, $externalId);

        return $this->organizationDataFromResponse($response, $externalId);
    }

    public function fetchReviewsPage(string $externalId, int $page): FetchedReviewsPage
    {
        if ($page < 1) {
            throw new InvalidArgumentException('The reviews page must be greater than zero.');
        }

        $response = $this->fetchReviewsResponse($externalId, $page);
        $this->ensureSuccessfulResponse($response, $externalId);

        return $this->reviewsPageFromResponse($response, $page);
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

        $path = '/'.ltrim($uri->path(), '/');

        if (preg_match(
            '~^/maps/org/[^/]+/(\d+)(?:/.*)?$~',
            $path,
            $matches,
        )) {
            return $matches[1];
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
        $organization = $this->organizationFromResponse($response, $externalId);

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

    /**
     * @param  array<string, mixed>  $query
     */
    private function get(string $url, array $query = []): Response
    {
        return Http::withHeaders([
            'Accept' => 'text/html,application/xhtml+xml',
            'Accept-Language' => 'ru-RU,ru;q=0.9',
        ])
            ->withUserAgent(self::USER_AGENT)
            ->connectTimeout(3)
            ->timeout(10)
            ->get($url, $query);
    }

    private function fetchReviewsResponse(string $externalId, int $page): Response
    {
        $request = $this->reviewsRequest($externalId);
        $csrfResponse = $request->post(self::REVIEWS_API_URL);
        $csrfResponse->throw();

        try {
            $csrfToken = $csrfResponse->json('csrfToken');
        } catch (JsonException $exception) {
            throw UnexpectedYandexMapsResponseException::create($exception);
        }

        if (! is_string($csrfToken) || $csrfToken === '') {
            throw UnexpectedYandexMapsResponseException::create();
        }

        $milliseconds = (int) floor(microtime(true) * 1000);
        $query = [
            'ajax' => 1,
            'businessId' => $externalId,
            'csrfToken' => $csrfToken,
            'locale' => 'ru_RU',
            'page' => $page,
            'pageSize' => 50,
            'ranking' => 'by_relevance_org',
            'reqId' => $milliseconds.'-'.random_int(1000000000, 9999999999).'-sas1-0000',
            'sessionId' => $milliseconds.'_'.random_int(100000, 999999),
        ];
        $query['s'] = $this->reviewsRequestSignature($query);

        return $request->get(self::REVIEWS_API_URL, $query);
    }

    private function reviewsRequest(string $externalId): PendingRequest
    {
        return Http::withOptions([
            'cookies' => new CookieJar,
        ])->withHeaders([
            'Accept' => 'application/json',
            'Accept-Language' => 'ru-RU,ru;q=0.9',
            'Origin' => 'https://yandex.ru',
            'Referer' => sprintf(self::ORGANIZATION_REVIEWS_URL, $externalId),
            'X-Requested-With' => 'XMLHttpRequest',
        ])
            ->withUserAgent(self::USER_AGENT)
            ->connectTimeout(3)
            ->timeout(10);
    }

    /**
     * @param  array<string, int|string>  $query
     */
    private function reviewsRequestSignature(array $query): string
    {
        ksort($query);

        $canonicalQuery = http_build_query(
            $query,
            '',
            '&',
            PHP_QUERY_RFC3986,
        );
        $hash = 5381;

        foreach (str_split($canonicalQuery) as $character) {
            $hash = (($hash * 33) ^ ord($character)) & 0xFFFFFFFF;
        }

        return (string) $hash;
    }

    private function ensureSuccessfulResponse(Response $response, string $externalId): void
    {
        if ($response->notFound()) {
            throw OrganizationNotFoundException::forExternalId($externalId);
        }

        $response->throw();
    }

    /**
     * @return array<string, mixed>
     */
    private function organizationFromResponse(Response $response, string $externalId): array
    {
        $state = $this->stateFromResponse($response);
        $organization = Arr::get($state, 'stack.0.results.items.0');

        if (! is_array($organization)
            || (string) Arr::get($organization, 'id') !== $externalId) {
            throw OrganizationNotFoundException::forExternalId($externalId);
        }

        return $organization;
    }

    /**
     * @return array<string, mixed>
     */
    private function stateFromResponse(Response $response): array
    {
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

        if (! is_array($state)) {
            throw UnexpectedYandexMapsResponseException::create();
        }

        return $state;
    }

    private function reviewsPageFromResponse(
        Response $response,
        int $requestedPage,
    ): FetchedReviewsPage {
        try {
            $reviewResults = $response->json('data');
        } catch (JsonException $exception) {
            throw UnexpectedYandexMapsResponseException::create($exception);
        }

        $reviews = Arr::get($reviewResults, 'reviews');
        $params = Arr::get($reviewResults, 'params');

        if (! is_array($reviewResults) || ! is_array($reviews) || ! is_array($params)) {
            throw UnexpectedYandexMapsResponseException::create();
        }

        $page = Arr::get($params, 'page');
        $limit = Arr::get($params, 'limit');
        $totalPages = Arr::get($params, 'totalPages');
        $totalReviews = Arr::get($params, 'count');
        $loadedReviewsCount = Arr::get($params, 'loadedReviewsCount');
        $reviewsRemained = Arr::get($params, 'reviewsRemained');

        if (! is_int($page)
            || $page !== $requestedPage
            || ! is_int($limit)
            || $limit < 1
            || ! is_int($totalPages)
            || $totalPages < 0
            || ! is_int($totalReviews)
            || $totalReviews < 0
            || ($totalReviews > 0 && $totalPages < $page)
            || ! is_int($loadedReviewsCount)
            || $loadedReviewsCount < 0
            || ! is_int($reviewsRemained)
            || $reviewsRemained < 0) {
            throw UnexpectedYandexMapsResponseException::create();
        }

        return new FetchedReviewsPage(
            reviews: array_map(
                fn (mixed $review): FetchedReview => $this->reviewFromData($review),
                array_values($reviews),
            ),
            page: $page,
            limit: $limit,
            totalPages: $totalPages,
            totalReviews: $totalReviews,
            loadedReviewsCount: $loadedReviewsCount,
            reviewsRemained: $reviewsRemained,
        );
    }

    private function reviewFromData(mixed $review): FetchedReview
    {
        if (! is_array($review)) {
            throw UnexpectedYandexMapsResponseException::create();
        }

        $externalId = Arr::get($review, 'reviewId');
        $authorName = Arr::get($review, 'author.name');
        $authorAvatarUrl = Arr::get($review, 'author.avatarUrl');
        $rating = Arr::get($review, 'rating');
        $text = Arr::get($review, 'text');
        $likesCount = Arr::get($review, 'reactions.likes', 0);
        $dislikesCount = Arr::get($review, 'reactions.dislikes', 0);
        $updatedTime = Arr::get($review, 'updatedTime');

        if (! is_string($externalId)
            || $externalId === ''
            || ($authorName !== null && ! is_string($authorName))
            || ($authorAvatarUrl !== null && ! is_string($authorAvatarUrl))
            || ! is_int($rating)
            || $rating < 1
            || $rating > 5
            || ($text !== null && ! is_string($text))
            || ! is_int($likesCount)
            || $likesCount < 0
            || ! is_int($dislikesCount)
            || $dislikesCount < 0
            || ! is_string($updatedTime)
            || $updatedTime === '') {
            throw UnexpectedYandexMapsResponseException::create();
        }

        try {
            $sourceUpdatedAt = CarbonImmutable::parse($updatedTime);
        } catch (Throwable $exception) {
            throw UnexpectedYandexMapsResponseException::create($exception);
        }

        return new FetchedReview(
            externalId: $externalId,
            authorName: $authorName,
            authorAvatarUrl: $authorAvatarUrl,
            rating: $rating,
            text: $text,
            likesCount: $likesCount,
            dislikesCount: $dislikesCount,
            sourceUpdatedAt: $sourceUpdatedAt,
        );
    }
}
