@extends('layouts.app')

@section('content')
    @include('partials.page-header', [
        'eyebrow' => __('nav.blog'),
        'title' => __('seo.blog_title'),
        'subtitle' => __('seo.blog_description'),
        'breadcrumbs' => [
            ['title' => __('nav.home'), 'url' => route('home')],
            ['title' => __('nav.blog'), 'url' => route('blog.index')],
        ],
    ])

    <div class="container-page py-12">
        @if ($articles->isEmpty())
            <x-empty-state :title="__('ui.no_results')" icon="document" />
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>

            <div class="mt-10">{{ $articles->links() }}</div>
        @endif
    </div>
@endsection
