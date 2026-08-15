<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\ProductRepository;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\SchemaBuilder;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends Controller
{
    public function __construct(private readonly ProductRepository $products) {}

    public function index(SeoService $seo): View
    {
        $seo->title(__('seo.products_title'))
            ->description(__('seo.products_description'))
            ->canonical(route('products.index'));

        return view('pages.products');
    }

    public function show(Category $category, string $product, SeoService $seo, SchemaBuilder $schema): View
    {
        $model = $this->products->findBySlug($product);

        if ($model === null) {
            throw new NotFoundHttpException;
        }

        // A product reached through the wrong category slug would be a
        // duplicate URL, so redirect-worthy mismatches are treated as misses.
        if ($model->category_id !== $category->id) {
            throw new NotFoundHttpException;
        }

        $this->products->incrementViews($model);

        $breadcrumbs = array_merge($category->breadcrumbs(), [
            ['title' => $model->name, 'url' => $model->url()],
        ]);

        $seo->title($model->seo_title ?: $model->name)
            ->description($model->seo_description ?: $model->short_description)
            ->image($model->coverUrl())
            ->canonical($model->url())
            ->type('product')
            ->breadcrumbs($breadcrumbs)
            ->schema($schema->product($model))
            ->schema($schema->breadcrumbs($breadcrumbs))
            ->schema($schema->faqPage($model->faqs));

        return view('pages.product', [
            'product' => $model,
            'related' => $this->products->relatedTo($model),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
