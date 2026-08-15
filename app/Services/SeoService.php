<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Collects the per-page SEO state during the request and hands it to the
 * layout. Pages set what they know; anything left unset falls back to sane
 * site-wide defaults rather than rendering an empty tag.
 */
class SeoService
{
    private ?string $title = null;

    private ?string $description = null;

    private ?string $image = null;

    private ?string $canonical = null;

    private string $type = 'website';

    private bool $noindex = false;

    /** @var array<int, array<string, mixed>> */
    private array $schemas = [];

    /** @var array<int, array{title: string, url: string|null}> */
    private array $breadcrumbs = [];

    public function title(?string $title): static
    {
        $this->title = $title ? Str::limit(strip_tags($title), 65, '') : null;

        return $this;
    }

    public function description(?string $description): static
    {
        $this->description = $description ? Str::limit(strip_tags($description), 158, '') : null;

        return $this;
    }

    public function image(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function canonical(?string $url): static
    {
        $this->canonical = $url;

        return $this;
    }

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function noindex(bool $noindex = true): static
    {
        $this->noindex = $noindex;

        return $this;
    }

    public function schema(?array $schema): static
    {
        if ($schema !== null) {
            $this->schemas[] = $schema;
        }

        return $this;
    }

    /** @param  array<int, array{title: string, url: string|null}>  $trail */
    public function breadcrumbs(array $trail): static
    {
        $this->breadcrumbs = $trail;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title
            ? $this->title.' | '.config('app.name')
            : config('app.name').' — '.__('seo.default_title');
    }

    public function getDescription(): string
    {
        return $this->description ?? __('seo.default_description');
    }

    public function getImage(): string
    {
        return $this->image ?? asset('images/og-default.svg');
    }

    public function getCanonical(): string
    {
        return $this->canonical ?? url()->current();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isNoindex(): bool
    {
        return $this->noindex;
    }

    /** @return array<int, array{title: string, url: string|null}> */
    public function getBreadcrumbs(): array
    {
        return $this->breadcrumbs;
    }

    /** @return array<int, array<string, mixed>> */
    public function getSchemas(): array
    {
        return $this->schemas;
    }

    /**
     * Alternate URLs for the fa/en pair. Locale lives in a `?lang=` free path
     * prefix so the canonical Persian URLs stay clean.
     */
    public function alternates(): array
    {
        $alternates = [];

        foreach (array_keys(config('site.locales')) as $locale) {
            $alternates[$locale] = $locale === config('app.fallback_locale')
                ? url()->current()
                : url()->current().'?lang='.$locale;
        }

        return $alternates;
    }
}
