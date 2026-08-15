<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\SchemaBuilder;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryController extends Controller
{
    public function show(Category $category, SeoService $seo, SchemaBuilder $schema): View
    {
        if (! $category->is_active) {
            throw new NotFoundHttpException;
        }

        $category->load(['children' => fn ($q) => $q->active()->ordered(), 'faqs']);

        $breadcrumbs = $category->breadcrumbs();

        $seo->title($category->seo_title ?: $category->name)
            ->description($category->seo_description ?: $category->short_description)
            ->canonical($category->url())
            ->type('website')
            ->breadcrumbs($breadcrumbs)
            ->schema($schema->breadcrumbs($breadcrumbs))
            ->schema($schema->faqPage($category->faqs));

        return view('pages.category', [
            'category' => $category,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
