<?php

declare(strict_types=1);

use App\Http\Controllers\Web\AdvisorController;
use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\BrandController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\CompareController;
use App\Http\Controllers\Web\DownloadController;
use App\Http\Controllers\Web\EnvironmentController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\KnowledgeController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\RentalController;
use App\Http\Controllers\Web\SelectorController;
use App\Http\Controllers\Web\ServiceController;
use App\Http\Controllers\Web\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// --- Catalog ---------------------------------------------------------------
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/compare', CompareController::class)->name('compare');
Route::get('/rental', RentalController::class)->name('rental');

// --- Find your machine -----------------------------------------------------
// The advisor is the front door for a buyer who knows the problem but not the
// machine; the calculator behind it is for one who already knows the metreage.
Route::get('/find-your-machine', AdvisorController::class)->name('advisor');
Route::get('/machine-selector', SelectorController::class)->name('selector');

// --- Services --------------------------------------------------------------
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{service:slug}', [ServiceController::class, 'show'])->name('service.show');

// --- Knowledge -------------------------------------------------------------
Route::get('/knowledge', [KnowledgeController::class, 'index'])->name('knowledge');

Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
Route::get('/brands/{brand:slug}', [BrandController::class, 'show'])->name('brand.show');

/*
 * Industry solutions. The section is named for what a buyer is looking for —
 * a solution for their site — rather than for the table behind it, and the old
 * /environments URLs redirect permanently so nothing already linked is lost.
 */
Route::get('/solutions', [EnvironmentController::class, 'index'])->name('environments.index');
Route::get('/solutions/{environment:slug}', [EnvironmentController::class, 'show'])->name('environment.show');
Route::permanentRedirect('/environments', '/solutions');
Route::get('/environments/{slug}', fn (string $slug) => redirect()->to('/solutions/'.$slug, 301));

// --- Content ---------------------------------------------------------------
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{blog:slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/downloads', [DownloadController::class, 'index'])->name('downloads.index');
Route::get('/downloads/{download}', [DownloadController::class, 'show'])->name('download.show');

Route::view('/about', 'pages.about')->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/thank-you', [PageController::class, 'thankYou'])->name('thank-you');

// --- SEO -------------------------------------------------------------------
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

/*
 * Categories sit at the URL root (/industrial-vacuum-cleaner) and products
 * hang off them (/industrial-vacuum-cleaner/ana-70l). These two patterns
 * swallow anything unmatched, so they must stay last in the file.
 */
Route::get('/{category:slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/{category:slug}/{product:slug}', [ProductController::class, 'show'])->name('product.show');
