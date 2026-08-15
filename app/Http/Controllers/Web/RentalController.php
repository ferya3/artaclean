<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;

class RentalController extends Controller
{
    public function __invoke(SeoService $seo): View
    {
        $seo->title(__('seo.rental_title'))
            ->description(__('seo.rental_description'))
            ->canonical(route('rental'));

        return view('pages.rental', [
            'products' => Product::query()
                ->active()
                ->rentable()
                ->with(['brand', 'category', 'rentalPlans'])
                ->ordered()
                ->paginate(12),
        ]);
    }
}
