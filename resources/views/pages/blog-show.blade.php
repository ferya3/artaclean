@extends('layouts.app')

@section('content')
    <article>
        <div class="container-page max-w-3xl pt-4">
            <x-breadcrumbs :items="$breadcrumbs" />

            @if ($article->category)
                <a href="{{ $article->category->url() }}" class="eyebrow">{{ $article->category->name }}</a>
            @endif

            <h1 class="mt-3 text-3xl leading-tight font-black text-ink-900 sm:text-4xl">{{ $article->title }}</h1>

            <div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-ink-400">
                @if ($article->author)
                    <span>{{ $article->author->name }}</span>
                @endif
                <span>{{ $article->published_at?->translatedFormat('j F Y') }}</span>
                <span>{{ __('ui.reading_time', ['minutes' => $article->reading_minutes]) }}</span>
            </div>
        </div>

        @if ($article->cover_image)
            <div class="container-page max-w-5xl py-8">
                <img src="{{ $article->coverUrl() }}" alt="{{ $article->title }}" fetchpriority="high"
                     class="aspect-16/9 w-full rounded-[var(--radius-card)] object-cover">
            </div>
        @endif

        <div class="container-page max-w-3xl pb-14">
            <div class="prose-article">{!! $article->body !!}</div>
        </div>
    </article>

    {{-- Machines the article talks about: the internal links that carry the page. --}}
    @if ($article->products->isNotEmpty())
        <section class="border-t border-ink-100 bg-ink-50 py-14">
            <div class="container-page">
                <x-section-heading :title="__('ui.product.related')" />

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($article->products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($related->isNotEmpty())
        <section class="section">
            <div class="container-page">
                <x-section-heading :title="__('ui.articles_title')" :href="route('blog.index')" />

                <div class="grid gap-6 sm:grid-cols-3">
                    @foreach ($related as $item)
                        <x-article-card :article="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
