<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Environment;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember(
            'sitemap.xml',
            config('site.cache.sitemap'),
            fn () => $this->build()
        );

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            // Session-scoped and staff-only paths carry no ranking value and
            // waste crawl budget.
            'Disallow: /admin',
            'Disallow: /dealer',
            'Disallow: /compare',
            'Disallow: /thank-you',
            'Disallow: /*?sort=',
            'Disallow: /*?page=',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain']);
    }

    private function build(): string
    {
        $urls = collect();

        $urls->push($this->url(route('home'), '1.0', 'daily'));

        foreach ([
            'products.index', 'selector', 'rental', 'environments.index',
            'brands.index', 'blog.index', 'downloads.index', 'faq', 'contact', 'about',
        ] as $name) {
            $urls->push($this->url(route($name), '0.8', 'weekly'));
        }

        Category::query()->active()->get()->each(
            fn (Category $category) => $urls->push(
                $this->url($category->url(), '0.9', 'weekly', $category->updated_at?->toAtomString())
            )
        );

        Environment::query()->active()->get()->each(
            fn (Environment $environment) => $urls->push(
                $this->url($environment->url(), '0.8', 'monthly', $environment->updated_at?->toAtomString())
            )
        );

        Brand::query()->active()->get()->each(
            fn (Brand $brand) => $urls->push(
                $this->url($brand->url(), '0.6', 'monthly', $brand->updated_at?->toAtomString())
            )
        );

        Product::query()->active()->with('category')->chunk(500, function ($products) use ($urls) {
            foreach ($products as $product) {
                $urls->push($this->url($product->url(), '0.9', 'weekly', $product->updated_at?->toAtomString()));
            }
        });

        Blog::query()->published()->get()->each(
            fn (Blog $blog) => $urls->push(
                $this->url($blog->url(), '0.7', 'monthly', $blog->updated_at?->toAtomString())
            )
        );

        return '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
            .'xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n"
            .$urls->implode('')
            .'</urlset>';
    }

    private function url(string $loc, string $priority, string $frequency, ?string $lastmod = null): string
    {
        $alternates = '';

        foreach (config('site.locales') as $locale => $meta) {
            $href = $locale === config('app.fallback_locale') ? $loc : $loc.'?lang='.$locale;
            $alternates .= sprintf(
                '    <xhtml:link rel="alternate" hreflang="%s" href="%s"/>'."\n",
                $meta['hreflang'],
                htmlspecialchars($href, ENT_XML1)
            );
        }

        return '  <url>'."\n"
            .'    <loc>'.htmlspecialchars($loc, ENT_XML1).'</loc>'."\n"
            .($lastmod ? '    <lastmod>'.$lastmod.'</lastmod>'."\n" : '')
            .'    <changefreq>'.$frequency.'</changefreq>'."\n"
            .'    <priority>'.$priority.'</priority>'."\n"
            .$alternates
            .'  </url>'."\n";
    }
}
