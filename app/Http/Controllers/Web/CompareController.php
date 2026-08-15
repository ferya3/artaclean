<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;

class CompareController extends Controller
{
    public function __invoke(SeoService $seo): View
    {
        // A session-specific shortlist has no business in the index.
        $seo->title(__('seo.compare_title'))
            ->description(__('seo.compare_description'))
            ->noindex();

        return view('pages.compare');
    }
}
