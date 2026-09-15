<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;

/**
 * Spare parts get their own entrance rather than living as one more category
 * tile: the person looking for a squeegee blade owns the machine already and
 * is not shopping, they are fixing something.
 */
class PartsController extends Controller
{
    public function __invoke(SeoService $seo): View
    {
        $seo->title(__('seo.parts_title'))
            ->description(__('seo.parts_description'))
            ->canonical(route('spare-parts'));

        return view('pages.spare-parts', [
            'parts' => Product::query()
                ->active()
                ->with(['brand', 'category', 'environments'])
                ->whereHas('category', fn ($q) => $q->where('slug', 'spare-parts'))
                ->ordered()
                ->get(),
        ]);
    }
}
