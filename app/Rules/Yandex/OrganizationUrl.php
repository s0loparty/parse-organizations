<?php

namespace App\Rules\Yandex;

use App\Services\Organizations\Resolvers\YandexOrganizationUrlResolver;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final readonly class OrganizationUrl implements ValidationRule
{
    private const string MESSAGE = 'The :attribute must be a valid Yandex organization URL.';

    public function __construct(
        private YandexOrganizationUrlResolver $resolver,
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! $this->resolver->supports($value)) {
            $fail(self::MESSAGE);
        }
    }
}
