<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;

class BrandController extends Controller
{
    public function index(SeoService $seo): View
    {
        $seo->title(__('seo.brands_title'))
            ->description(__('seo.brands_description'))
            ->canonical(route('brands.index'));

        return view('pages.brands', [
            'brands' => Brand::query()->active()->ordered()->withCount('products')->get(),
        ]);
    }

    public function show(Brand $brand, SeoService $seo): View
    {
        abort_unless($brand->is_active, 404);

        $seo->title(__('seo.brand_title', ['name' => $brand->name]))
            ->description($brand->description)
            ->canonical($brand->url());

        return view('pages.brand', ['brand' => $brand]);
    }
}
