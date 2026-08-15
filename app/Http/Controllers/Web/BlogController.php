<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\BlogRepository;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Services\SchemaBuilder;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(private readonly BlogRepository $blogs) {}

    public function index(Request $request, SeoService $seo): View
    {
        $seo->title(__('seo.blog_title'))
            ->description(__('seo.blog_description'))
            ->canonical(route('blog.index'));

        return view('pages.blog-index', [
            'articles' => $this->blogs->paginate(9, $request->query('category')),
        ]);
    }

    public function show(string $blog, SeoService $seo, SchemaBuilder $schema): View
    {
        $article = $this->blogs->findBySlug($blog);

        abort_if($article === null, 404);

        Blog::withoutTimestamps(fn () => $article->increment('views_count'));

        $breadcrumbs = [
            ['title' => __('nav.home'), 'url' => route('home')],
            ['title' => __('nav.blog'), 'url' => route('blog.index')],
            ['title' => $article->title, 'url' => $article->url()],
        ];

        $seo->title($article->seo_title ?: $article->title)
            ->description($article->seo_description ?: $article->excerpt)
            ->image($article->coverUrl())
            ->canonical($article->url())
            ->type('article')
            ->breadcrumbs($breadcrumbs)
            ->schema($schema->article($article))
            ->schema($schema->breadcrumbs($breadcrumbs));

        return view('pages.blog-show', [
            'article' => $article,
            'related' => $this->blogs->relatedTo($article),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
