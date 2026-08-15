<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Environment;
use App\Models\Product;
use App\Observers\CatalogCacheObserver;
use App\Services\SeoService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // One SEO bag per request; controllers and Livewire components fill it
        // and the layout reads it back out.
        $this->app->scoped(SeoService::class);
    }

    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
        Model::automaticallyEagerLoadRelationships();

        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.simple-tailwind');

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        foreach ([Product::class, Category::class, Brand::class, Environment::class] as $model) {
            $model::observe(CatalogCacheObserver::class);
        }

        View::share('seo', $this->app->make(SeoService::class));
    }
}
