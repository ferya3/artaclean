<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\CatalogSeeder;
use Database\Seeders\ContentSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Generated URLs must follow the scheme the visitor actually used.
 *
 * A blanket `URL::forceScheme('https')` for any production environment once
 * pointed every asset and link at port 443 during the window between
 * provisioning and certbot: the page returned 200 with no working stylesheet
 * and every link led to a refused connection, including when browsing by IP.
 * AppServiceProvider now keys the scheme off APP_URL instead.
 *
 * phpunit.xml pins APP_URL to http://localhost so these assertions describe the
 * unforced path deterministically, rather than inheriting whatever the local
 * .env happens to hold. The forced path is the trivial one — it is the unforced
 * path that broke a live server.
 */
class UrlSchemeTest extends TestCase
{
    use RefreshDatabase;

    /** True when this test wrote the manifest and therefore has to remove it. */
    private bool $manifestIsOurs = false;

    protected function setUp(): void
    {
        parent::setUp();

        /*
         * The base case switches Vite off so the suite does not depend on a
         * bundler run. This file is the one that asserts on the asset URLs
         * themselves — the https-without-a-certificate bug it guards against
         * showed up precisely as a stylesheet URL — so it needs Vite back, and
         * a manifest for Vite to read.
         *
         * A throwaway manifest keeps that hermetic: the assertions are about
         * the scheme and host Laravel builds the URL from, never about what a
         * build produced. A real one already on disk is left alone.
         */
        $this->withVite();
        $this->writeManifestIfMissing();

        $this->seed([RoleSeeder::class, UserSeeder::class, CatalogSeeder::class, ProductSeeder::class, ContentSeeder::class]);
    }

    protected function tearDown(): void
    {
        if ($this->manifestIsOurs) {
            File::deleteDirectory(public_path('build'));
            $this->manifestIsOurs = false;
        }

        parent::tearDown();
    }

    private function writeManifestIfMissing(): void
    {
        $manifest = public_path('build/manifest.json');

        if (File::exists($manifest)) {
            return;
        }

        File::ensureDirectoryExists(dirname($manifest));
        File::put($manifest, json_encode([
            'resources/css/app.css' => [
                'file' => 'assets/app.css',
                'src' => 'resources/css/app.css',
                'isEntry' => true,
            ],
            'resources/js/app.js' => [
                'file' => 'assets/app.js',
                'src' => 'resources/js/app.js',
                'isEntry' => true,
            ],
        ], JSON_THROW_ON_ERROR));

        $this->manifestIsOurs = true;
    }

    public function test_the_test_environment_pins_a_plain_http_app_url(): void
    {
        // Guards the premise of every other case in this file.
        $this->assertSame('http://localhost', config('app.url'));
    }

    public function test_a_plain_http_visit_gets_http_urls(): void
    {
        $this->get('http://localhost/')
            ->assertOk()
            ->assertSee('href="http://localhost/build/', false)
            ->assertDontSee('href="https://localhost/', false)
            ->assertDontSee('src="https://localhost/', false);
    }

    public function test_a_secure_visit_gets_https_urls(): void
    {
        $this->get('https://localhost/')
            ->assertOk()
            ->assertSee('href="https://localhost/build/', false)
            ->assertDontSee('href="http://localhost/', false);
    }

    public function test_the_canonical_tag_follows_the_request_scheme(): void
    {
        $this->get('http://localhost/products')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="http://localhost/products">', false);

        $this->get('https://localhost/products')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://localhost/products">', false);
    }

    /**
     * Browsing by bare IP is how a server gets checked before DNS propagates,
     * so it has to produce working links rather than pointing at a port that
     * nothing is listening on.
     */
    public function test_browsing_by_ip_produces_working_links(): void
    {
        $this->get('http://5.223.57.134/')
            ->assertOk()
            ->assertSee('href="http://5.223.57.134/build/', false)
            ->assertDontSee('href="https://5.223.57.134/', false);
    }
}
