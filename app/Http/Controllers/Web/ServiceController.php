<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\SchemaBuilder;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;

/**
 * After-sales is not a footer link in this trade — installation, servicing,
 * parts and training are a large part of what a procurement manager is
 * choosing a supplier on, so they get pages of their own.
 */
class ServiceController extends Controller
{
    public function index(SeoService $seo): View
    {
        $seo->title(__('seo.services_title'))
            ->description(__('seo.services_description'))
            ->canonical(route('services.index'));

        return view('pages.services', [
            'services' => Service::query()->active()->ordered()->get(),
        ]);
    }

    public function show(Service $service, SeoService $seo, SchemaBuilder $schema): View
    {
        abort_unless($service->is_active, 404);

        $breadcrumbs = $service->breadcrumbs();

        $seo->title($service->seo_title ?: __('seo.service_title', ['name' => $service->name]))
            ->description($service->seo_description ?: $service->short_description)
            ->canonical($service->url())
            ->breadcrumbs($breadcrumbs)
            ->schema($schema->breadcrumbs($breadcrumbs));

        return view('pages.service', [
            'service' => $service,
            'others' => Service::query()->active()->ordered()->whereKeyNot($service->id)->get(),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
