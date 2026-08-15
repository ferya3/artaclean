@extends('layouts.app')

@section('content')
    @include('partials.page-header', [
        'eyebrow' => __('nav.environments'),
        'title' => __('seo.environments_title'),
        'subtitle' => __('seo.environments_description'),
        'breadcrumbs' => [
            ['title' => __('nav.home'), 'url' => route('home')],
            ['title' => __('nav.environments'), 'url' => route('environments.index')],
        ],
    ])

    <div class="container-page py-12">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($environments as $environment)
                <a href="{{ $environment->url() }}" class="card-hover group overflow-hidden">
                    <div class="relative aspect-16/9 bg-ink-900">
                        @if ($environment->image)
                            <img src="{{ Storage::url($environment->image) }}" alt="{{ $environment->name }}"
                                 loading="lazy"
                                 class="size-full object-cover opacity-70 transition-transform duration-500 group-hover:scale-105">
                        @endif
                    </div>

                    <div class="p-5">
                        <h2 class="text-base font-bold text-ink-900 group-hover:text-brand-700">{{ $environment->name }}</h2>

                        @if ($environment->short_description)
                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-ink-500">{{ $environment->short_description }}</p>
                        @endif

                        <p class="tabular mt-4 text-xs text-ink-400">
                            {{ __('ui.results_count', ['count' => $environment->products_count]) }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endsection
