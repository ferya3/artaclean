<?php

declare(strict_types=1);

namespace App\CQRS;

interface CacheableQuery extends Query
{
    public function cacheKey(): string;

    public function cacheTtl(): int;

    /** @return array<int, string> */
    public function cacheTags(): array;
}
