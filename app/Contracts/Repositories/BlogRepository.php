<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Blog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface BlogRepository
{
    public function findBySlug(string $slug): ?Blog;

    public function paginate(int $perPage = 9, ?string $category = null): LengthAwarePaginator;

    public function latest(int $limit = 3): Collection;

    public function relatedTo(Blog $blog, int $limit = 3): Collection;
}
