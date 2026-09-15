<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\KnowledgeType;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;

/**
 * One feed of "blog posts" buries a buying guide behind a maintenance tip.
 * The hub shelves them by what they answer instead: guides, comparisons,
 * finished projects, video.
 */
class KnowledgeController extends Controller
{
    public function index(SeoService $seo): View
    {
        $seo->title(__('seo.knowledge_title'))
            ->description(__('seo.knowledge_description'))
            ->canonical(route('knowledge'));

        $articles = Blog::query()
            ->published()
            ->with('category')
            ->latest('published_at')
            ->get();

        return view('pages.knowledge', [
            // Keyed by type so the view can render a shelf per kind and skip
            // the kinds that have nothing in them yet.
            'shelves' => collect(KnowledgeType::cases())
                ->mapWithKeys(fn (KnowledgeType $type) => [
                    $type->value => $articles->where('type', $type),
                ])
                ->filter->isNotEmpty(),
            'latest' => $articles->take(3),
        ]);
    }
}
