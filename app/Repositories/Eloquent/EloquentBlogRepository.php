<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\BlogRepository;
use App\Models\Blog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentBlogRepository implements BlogRepository
{
    public function findBySlug(string $slug): ?Blog
    {
        return Blog::query()
            ->published()
            ->with(['author', 'category', 'products.brand'])
            ->where('slug', $slug)
            ->first();
    }

    public function paginate(int $perPage = 9, ?string $category = null): LengthAwarePaginator
    {
        return Blog::query()
            ->published()
            ->with('category')
            ->when($category, fn (Builder $q) => $q->whereHas('category', fn (Builder $c) => $c->where('slug', $category)))
            ->latest('published_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function latest(int $limit = 3): Collection
    {
        return Blog::query()
            ->published()
            ->with('category')
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function relatedTo(Blog $blog, int $limit = 3): Collection
    {
        return Blog::query()
            ->published()
            ->whereKeyNot($blog->getKey())
            ->when($blog->category_id, fn (Builder $q) => $q->where('category_id', $blog->category_id))
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }
}
