<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Environment;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;

class SelectorController extends Controller
{
    public function __invoke(SeoService $seo): View
    {
        $seo->title(__('seo.selector_title'))
            ->description(__('seo.selector_description'))
            ->canonical(route('selector'));

        return view('pages.selector', [
            'environments' => Environment::query()->active()->ordered()->get(),
        ]);
    }
}
