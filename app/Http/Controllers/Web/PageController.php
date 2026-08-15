<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Services\SchemaBuilder;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function contact(SeoService $seo): View
    {
        $seo->title(__('seo.contact_title'))
            ->description(__('seo.contact_description'))
            ->canonical(route('contact'));

        return view('pages.contact');
    }

    public function faq(SeoService $seo, SchemaBuilder $schema): View
    {
        $faqs = Faq::query()->active()->global()->orderBy('sort_order')->get();

        $seo->title(__('seo.faq_title'))
            ->description(__('seo.faq_description'))
            ->canonical(route('faq'))
            ->schema($schema->faqPage($faqs));

        return view('pages.faq', ['faqs' => $faqs]);
    }

    public function thankYou(SeoService $seo): View
    {
        $seo->title(__('seo.thank_you_title'))->noindex();

        return view('pages.thank-you');
    }
}
