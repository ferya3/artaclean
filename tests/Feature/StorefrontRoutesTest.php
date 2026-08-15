<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\CatalogSeeder;
use Database\Seeders\ContentSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StorefrontRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, UserSeeder::class, CatalogSeeder::class, ProductSeeder::class, ContentSeeder::class]);
    }

    public static function publicRoutes(): array
    {
        return [
            'home' => ['/'],
            'products' => ['/products'],
            'category' => ['/scrubber-dryer'],
            'nested category' => ['/wet-dry-vacuum'],
            'product' => ['/scrubber-dryer/arta-scrub-70'],
            'compare' => ['/compare'],
            'selector' => ['/machine-selector'],
            'rental' => ['/rental'],
            'environments' => ['/environments'],
            'environment' => ['/environments/factory'],
            'brands' => ['/brands'],
            'brand' => ['/brands/karcher'],
            'blog' => ['/blog'],
            'article' => ['/blog/how-to-choose-industrial-scrubber'],
            'downloads' => ['/downloads'],
            'faq' => ['/faq'],
            'contact' => ['/contact'],
            'about' => ['/about'],
            'sitemap' => ['/sitemap.xml'],
            'robots' => ['/robots.txt'],
        ];
    }

    #[DataProvider('publicRoutes')]
    public function test_public_pages_respond(string $path): void
    {
        $this->get($path)->assertOk();
    }

    public function test_a_product_reached_through_the_wrong_category_is_a_miss(): void
    {
        // Otherwise the same machine would be reachable at several URLs.
        $this->get('/industrial-vacuum-cleaner/arta-scrub-70')->assertNotFound();
    }

    public function test_an_unknown_slug_is_a_miss(): void
    {
        $this->get('/no-such-category')->assertNotFound();
    }

    public function test_the_product_page_emits_product_and_faq_schema(): void
    {
        $response = $this->get('/scrubber-dryer/arta-scrub-70');

        $response->assertOk()
            ->assertSee('"@type":"Product"', false)
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('"availability":"https://schema.org/InStock"', false);
    }

    public function test_prices_stay_hidden_behind_the_quote_call_to_action(): void
    {
        $this->get('/scrubber-dryer/arta-scrub-70')
            ->assertOk()
            ->assertSee(__('ui.price_on_request'))
            ->assertDontSee('720,000,000');
    }

    public function test_the_comparison_page_is_excluded_from_the_index(): void
    {
        $this->get('/compare')->assertOk()->assertSee('noindex', false);
    }

    public function test_the_sitemap_lists_categories_and_products(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('/scrubber-dryer', false)
            ->assertSee('/scrubber-dryer/arta-scrub-70', false)
            ->assertSee('hreflang="en"', false);
    }

    public function test_robots_blocks_the_panels_and_points_at_the_sitemap(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Disallow: /dealer')
            ->assertSee('Sitemap: ');
    }

    public function test_the_language_switch_flips_the_document_direction(): void
    {
        $this->get('/')->assertSee('<html lang="fa" dir="rtl">', false);
        $this->get('/?lang=en')->assertSee('<html lang="en" dir="ltr">', false);
    }

    public function test_the_chosen_language_persists_across_requests(): void
    {
        $this->get('/?lang=en');
        $this->get('/products')->assertSee('<html lang="en" dir="ltr">', false);
    }
}
