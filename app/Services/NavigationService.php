<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\CategoryRepository;
use App\Enums\NavGroup;
use App\Models\Brand;
use App\Models\Environment;
use App\Models\Service;
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

    /**
     * Categories keyed by the family they belong to, in enum order.
     *
     * Ten categories in one flat column is ten headings to read before the
     * first decision; four named groups of two or three is one glance. A
     * category with no group set still appears, under the family the rest of
     * the floor equipment sits in, so nothing can fall out of the menu by
     * being left unclassified.
     *
     * @return Collection<string, Collection>
     */
    public function categoryGroups(): Collection
    {
        return $this->remember('nav.category_groups', function (): Collection {
            $categories = $this->categories->tree();

            return collect(NavGroup::cases())
                ->mapWithKeys(fn (NavGroup $group) => [
                    $group->value => $categories->filter(
                        fn ($category) => ($category->nav_group?->value ?? NavGroup::Floor->value) === $group->value
                    )->values(),
                ])
                ->filter->isNotEmpty();
        });
    }

    public function services(): Collection
    {
        return $this->remember(
            'nav.services',
            fn () => Service::query()->active()->ordered()->get()
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
        foreach (['categories', 'category_groups', 'environments', 'brands', 'services'] as $key) {
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
