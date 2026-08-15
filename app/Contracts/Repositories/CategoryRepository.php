<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Category;
use Illuminate\Support\Collection;

interface CategoryRepository
{
    public function findBySlug(string $slug): ?Category;

    /** Root categories with their children, for the mega menu. */
    public function tree(): Collection;

    /** Root categories with a product count, for the home page grid. */
    public function forHomeGrid(): Collection;
}
