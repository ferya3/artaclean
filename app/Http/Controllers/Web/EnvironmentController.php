<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\ProductRepository;
use App\Http\Controllers\Controller;
use App\Models\Environment;
use App\Services\SchemaBuilder;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;

class EnvironmentController extends Controller
{
    public function index(SeoService $seo): View
    {
        $seo->title(__('seo.environments_title'))
            ->description(__('seo.environments_description'))
            ->canonical(route('environments.index'));

        return view('pages.environments', [
            'environments' => Environment::query()->active()->ordered()->withCount('products')->get(),
        ]);
    }

    public function show(
        Environment $environment,
        ProductRepository $products,
        SeoService $seo,
        SchemaBuilder $schema,
    ): View {
        abort_unless($environment->is_active, 404);

        $environment->load('faqs');

        $breadcrumbs = [
            ['title' => __('nav.home'), 'url' => route('home')],
            ['title' => __('nav.environments'), 'url' => route('environments.index')],
            ['title' => $environment->name, 'url' => $environment->url()],
        ];

        $seo->title($environment->seo_title ?: __('seo.environment_title', ['name' => $environment->name]))
            ->description($environment->seo_description ?: $environment->short_description)
            ->canonical($environment->url())
            ->breadcrumbs($breadcrumbs)
            ->schema($schema->breadcrumbs($breadcrumbs))
            ->schema($schema->faqPage($environment->faqs));

        return view('pages.environment', [
            'environment' => $environment,
            'products' => $products->forEnvironment($environment->id, 12),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
