<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Blog;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Builds the JSON-LD blocks. Everything a competitor in this niche typically
 * skips: Product with real technical properties, FAQPage, BreadcrumbList,
 * Organization and WebSite.
 */
class SchemaBuilder
{
    public function organization(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => url('/#organization'),
            'name' => config('app.name'),
            'url' => url('/'),
            'logo' => asset('images/logo.svg'),
            'telephone' => config('site.phone_e164'),
            'email' => config('site.email'),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => config('site.address'),
                'addressCountry' => 'IR',
            ],
            'sameAs' => array_values(array_filter([
                config('site.instagram'),
                config('site.linkedin'),
                config('site.aparat'),
            ])),
        ];
    }

    public function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => url('/#website'),
            'url' => url('/'),
            'name' => config('app.name'),
            'inLanguage' => app()->getLocale(),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => route('products.index').'?search={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    public function product(Product $product): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            '@id' => $product->url().'#product',
            'name' => $product->name,
            'description' => $product->short_description ?? strip_tags((string) $product->description),
            'sku' => $product->sku,
            'mpn' => $product->model_code,
            'url' => $product->url(),
            'category' => $product->category?->name,
            'image' => $this->productImages($product),
            'additionalProperty' => $this->propertyValues($product),
        ];

        if ($product->brand) {
            $schema['brand'] = [
                '@type' => 'Brand',
                'name' => $product->brand->name,
            ];
        }

        if ($product->warranty_months) {
            $schema['warranty'] = [
                '@type' => 'WarrantyPromise',
                'durationOfWarranty' => [
                    '@type' => 'QuantitativeValue',
                    'value' => $product->warranty_months,
                    'unitCode' => 'MON',
                ],
            ];
        }

        // Prices are hidden by policy, so the Offer advertises availability and
        // a quote URL rather than publishing a number that would go stale.
        $offer = [
            '@type' => 'Offer',
            'url' => $product->url(),
            'priceCurrency' => $product->currency,
            'availability' => $product->stock_status->schemaAvailability(),
            'seller' => ['@id' => url('/#organization')],
        ];

        if ($product->showsPrice()) {
            $offer['price'] = (string) (int) $product->price;
        } else {
            $offer['priceSpecification'] = [
                '@type' => 'PriceSpecification',
                'priceCurrency' => $product->currency,
                'valueAddedTaxIncluded' => false,
            ];
        }

        $schema['offers'] = $offer;

        return $schema;
    }

    /** @param  Collection<int, Faq>  $faqs */
    public function faqPage(Collection $faqs): ?array
    {
        $entries = $faqs->where('in_schema', true);

        if ($entries->isEmpty()) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $entries->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq->question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strip_tags((string) $faq->answer),
                ],
            ])->values()->all(),
        ];
    }

    /** @param  array<int, array{title: string, url: string|null}>  $trail */
    public function breadcrumbs(array $trail): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($trail)->values()->map(fn (array $crumb, int $index) => array_filter([
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb['title'],
                'item' => $crumb['url'],
            ]))->all(),
        ];
    }

    public function article(Blog $blog): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $blog->title,
            'description' => $blog->excerpt,
            'image' => $blog->coverUrl(),
            'datePublished' => $blog->published_at?->toIso8601String(),
            'dateModified' => $blog->updated_at?->toIso8601String(),
            'author' => [
                '@type' => $blog->author ? 'Person' : 'Organization',
                'name' => $blog->author?->name ?? config('app.name'),
            ],
            'publisher' => ['@id' => url('/#organization')],
            'mainEntityOfPage' => $blog->url(),
        ];
    }

    /** @param  Collection<int, Product>  $products */
    public function itemList(Collection $products, Category $category): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $category->name,
            'numberOfItems' => $products->count(),
            'itemListElement' => $products->values()->map(fn (Product $product, int $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'url' => $product->url(),
                'name' => $product->name,
            ])->all(),
        ];
    }

    private function productImages(Product $product): array
    {
        $images = collect([$product->coverUrl()]);

        if ($product->relationLoaded('galleryImages')) {
            $images = $images->merge($product->galleryImages->map(fn ($image) => $image->url()));
        }

        return $images->filter()->unique()->values()->all();
    }

    private function propertyValues(Product $product): array
    {
        return $product->keySpecs()->map(fn (array $spec) => [
            '@type' => 'PropertyValue',
            'name' => $spec['label'],
            'value' => $spec['value'],
        ])->all();
    }
}
