<?php

declare(strict_types=1);

namespace App\CQRS\Bus;

use App\CQRS\CacheableQuery;
use App\CQRS\Query;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;

class QueryBus extends MessageBus
{
    /**
     * A query that opts into CacheableQuery gets its result memoised under a
     * key it derives from its own parameters. Redis (the production store)
     * supports tags so a single product save can flush a whole family of
     * cached reads; file and database stores fall back to plain keys.
     */
    public function ask(Query $query): mixed
    {
        $handler = $this->resolveHandler($query);

        if (! $query instanceof CacheableQuery) {
            return $handler->handle($query);
        }

        $repository = Cache::store();

        if ($repository->getStore() instanceof TaggableStore && $query->cacheTags() !== []) {
            $repository = $repository->tags($query->cacheTags());
        }

        return $repository->remember(
            $query->cacheKey(),
            $query->cacheTtl(),
            fn () => $handler->handle($query),
        );
    }
}
