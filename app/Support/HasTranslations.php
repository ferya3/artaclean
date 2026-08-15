<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Stores every translatable attribute as a JSON map keyed by locale:
 *
 *     {"fa": "جاروبرقی صنعتی", "en": "Industrial Vacuum Cleaner"}
 *
 * Reading `$product->name` gives the string for the active locale (falling
 * back to the app fallback locale, then to the first value present), while
 * `$product->attributesToArray()['name']` still yields the whole map — which
 * is what the Filament panel binds its `name.fa` / `name.en` fields to.
 *
 * The consuming model must declare:
 *
 *     protected array $translatable = ['name', 'description'];
 *
 * and cast those same attributes to `array`.
 */
trait HasTranslations
{
    public function translatable(): array
    {
        return $this->translatable ?? [];
    }

    public function isTranslatable(string $key): bool
    {
        return in_array($key, $this->translatable(), true);
    }

    public function getAttributeValue($key)
    {
        if (! $this->isTranslatable($key)) {
            return parent::getAttributeValue($key);
        }

        return $this->translate($key);
    }

    public function setAttribute($key, $value)
    {
        if ($this->isTranslatable($key) && is_string($value)) {
            $translations = $this->getTranslations($key);
            $translations[app()->getLocale()] = $value;

            return parent::setAttribute($key, $translations);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * The raw locale => value map for an attribute.
     */
    public function getTranslations(string $key): array
    {
        $value = parent::getAttributeValue($key);

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [config('app.fallback_locale') => $value];
        }

        return [];
    }

    public function translate(string $key, ?string $locale = null): ?string
    {
        $translations = $this->getTranslations($key);

        if ($translations === []) {
            return null;
        }

        $locale ??= app()->getLocale();

        foreach ([$locale, config('app.fallback_locale'), 'fa', 'en'] as $candidate) {
            if (! empty($translations[$candidate])) {
                return $translations[$candidate];
            }
        }

        $first = reset($translations);

        return $first === false ? null : (string) $first;
    }

    public function setTranslation(string $key, string $locale, ?string $value): static
    {
        $translations = $this->getTranslations($key);
        $translations[$locale] = $value;

        parent::setAttribute($key, $translations);

        return $this;
    }

    /**
     * Matches a translated attribute against a term in every configured locale.
     *
     * The raw column holds JSON with unicode escape sequences, so it is queried
     * through the `->` path syntax — which compiles to json_extract on both
     * MySQL 8 and SQLite and therefore compares decoded text.
     */
    public function scopeWhereTranslationLike(Builder $query, string $key, string $term): Builder
    {
        return $query->where(function (Builder $inner) use ($key, $term) {
            foreach (array_keys(config('site.locales')) as $locale) {
                $inner->orWhere("{$key}->{$locale}", 'like', '%'.$term.'%');
            }
        });
    }
}
