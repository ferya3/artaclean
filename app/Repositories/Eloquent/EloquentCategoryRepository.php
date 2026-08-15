<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CategoryRepository;
use App\Models\Category;
use Illuminate\Support\Collection;

class EloquentCategoryRepository implements CategoryRepository
{
    public function findBySlug(string $slug): ?Category
    {
        return Category::query()
            ->active()
            ->with(['parent', 'children' => fn ($q) => $q->active(), 'faqs', 'attributes.values'])
            ->where('slug', $slug)
            ->first();
    }

    public function tree(): Collection
    {
        return Category::query()
            ->active()
            ->roots()
            ->inMenu()
            ->with(['children' => fn ($q) => $q->active()->inMenu()->ordered()])
            ->ordered()
            ->get();
    }

    public function forHomeGrid(): Collection
    {
        return Category::query()
            ->active()
            ->roots()
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->ordered()
            ->get();
    }
}
