<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\BlogRepository;
use App\Contracts\Repositories\CategoryRepository;
use App\Contracts\Repositories\LeadRepository;
use App\Contracts\Repositories\ProductRepository;
use App\Repositories\Eloquent\EloquentBlogRepository;
use App\Repositories\Eloquent\EloquentCategoryRepository;
use App\Repositories\Eloquent\EloquentLeadRepository;
use App\Repositories\Eloquent\EloquentProductRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    private const BINDINGS = [
        ProductRepository::class => EloquentProductRepository::class,
        CategoryRepository::class => EloquentCategoryRepository::class,
        BlogRepository::class => EloquentBlogRepository::class,
        LeadRepository::class => EloquentLeadRepository::class,
    ];

    public function register(): void
    {
        foreach (self::BINDINGS as $contract => $implementation) {
            $this->app->bind($contract, $implementation);
        }
    }

    /** @return array<int, string> */
    public function provides(): array
    {
        return array_keys(self::BINDINGS);
    }
}
