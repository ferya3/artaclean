<?php

declare(strict_types=1);

use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\BrandController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\CompareController;
use App\Http\Controllers\Web\DownloadController;
use App\Http\Controllers\Web\EnvironmentController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\RentalController;
use App\Http\Controllers\Web\SelectorController;
use App\Http\Controllers\Web\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// --- Catalog ---------------------------------------------------------------
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/compare', CompareController::class)->name('compare');
Route::get('/machine-selector', SelectorController::class)->name('selector');
Route::get('/rental', RentalController::class)->name('rental');

Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
Route::get('/brands/{brand:slug}', [BrandController::class, 'show'])->name('brand.show');

Route::get('/environments', [EnvironmentController::class, 'index'])->name('environments.index');
Route::get('/environments/{environment:slug}', [EnvironmentController::class, 'show'])->name('environment.show');

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
