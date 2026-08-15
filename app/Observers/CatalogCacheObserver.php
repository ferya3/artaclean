<?php

declare(strict_types=1);

namespace App\Observers;

use App\Services\NavigationService;
use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Editors expect the storefront to reflect a save immediately, so any write to
 * a catalog model drops the cached home page, navigation and facet buckets.
 */
class CatalogCacheObserver
{
    public function __construct(private readonly NavigationService $navigation) {}

    public function saved(Model $model): void
    {
        $this->flush();
    }

    public function deleted(Model $model): void
    {
        $this->flush();
    }

    private function flush(): void
    {
        $this->navigation->flush();

        $store = Cache::store();

        if ($store->getStore() instanceof TaggableStore) {
            $store->tags(['catalog'])->flush();

            return;
        }

        // Untagged stores (file, database) only need the explicit keys gone.
        foreach (array_keys(config('site.locales')) as $locale) {
            $store->forget('home.'.$locale);
        }
    }
}
