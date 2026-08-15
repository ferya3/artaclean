<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\CQRS\Bus\QueryBus;
use App\CQRS\Queries\GetHomePageData;
use App\Http\Controllers\Controller;
use App\Services\SchemaBuilder;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(QueryBus $bus, SeoService $seo, SchemaBuilder $schema): View
    {
        $data = $bus->ask(new GetHomePageData);

        $seo->title(__('seo.home_title'))
            ->description(__('seo.home_description'))
            ->canonical(route('home'))
            ->schema($schema->organization())
            ->schema($schema->website())
            ->schema($schema->faqPage($data['faqs']));

        return view('pages.home', $data);
    }
}
