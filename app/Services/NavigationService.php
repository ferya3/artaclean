<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\CategoryRepository;
use App\Models\Brand;
use App\Models\Environment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * The header/footer navigation is identical for every visitor, so it is built
 * once and cached rather than re-queried on every request.
 */
class NavigationService
{
    public function __construct(private readonly CategoryRepository $categories) {}

    public function categories(): Collection
    {
        return $this->remember('nav.categories', fn () => $this->categories->tree());
    }

    public function environments(): Collection
    {
        return $this->remember(
            'nav.environments',
            fn () => Environment::query()->active()->ordered()->get()
        );
    }

    public function brands(): Collection
    {
        return $this->remember(
            'nav.brands',
            fn () => Brand::query()->active()->ordered()->get()
        );
    }

    public function flush(): void
    {
        foreach (['categories', 'environments', 'brands'] as $key) {
            foreach (array_keys(config('site.locales')) as $locale) {
                Cache::forget("nav.{$key}.{$locale}");
            }
        }
    }

    private function remember(string $key, \Closure $callback): Collection
    {
        return Cache::remember(
            $key.'.'.app()->getLocale(),
            config('site.cache.navigation'),
            $callback
        );
    }
}
