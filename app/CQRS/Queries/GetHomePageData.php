<?php

declare(strict_types=1);

namespace App\CQRS\Queries;

use App\CQRS\CacheableQuery;

/**
 * The home page is the single hottest read in the app and changes only when an
 * editor touches content, so it is cached wholesale.
 */
final readonly class GetHomePageData implements CacheableQuery
{
    public function cacheKey(): string
    {
        return 'home.'.app()->getLocale();
    }

    public function cacheTtl(): int
    {
        return (int) config('site.cache.home');
    }

    public function cacheTags(): array
    {
        return ['catalog', 'home'];
    }
}
