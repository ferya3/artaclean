<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Environment;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;

/**
 * The page behind "چه دستگاهی نیاز دارید؟" — the entry point for every
 * visitor who knows their problem but not the machine that solves it.
 */
class AdvisorController extends Controller
{
    public function __invoke(SeoService $seo): View
    {
        $seo->title(__('seo.advisor_title'))
            ->description(__('seo.advisor_description'))
            ->canonical(route('advisor'));

        return view('pages.advisor', [
            'environments' => Environment::query()->active()->ordered()->take(8)->get(),
        ]);
    }
}
