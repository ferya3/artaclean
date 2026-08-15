@extends('layouts.app')

@section('content')
    @include('partials.page-header', [
        'title' => __('seo.brands_title'),
        'subtitle' => __('seo.brands_description'),
        'breadcrumbs' => [
            ['title' => __('nav.home'), 'url' => route('home')],
            ['title' => __('nav.brands'), 'url' => route('brands.index')],
        ],
    ])

    <div class="container-page py-12">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($brands as $brand)
                <a href="{{ $brand->url() }}" class="card-hover flex flex-col items-center p-8 text-center">
                    @if ($brand->logo)
                        <img src="{{ Storage::url($brand->logo) }}" alt="{{ $brand->name }}" loading="lazy" class="h-12 w-auto object-contain">
                    @else
                        <span class="text-lg font-black text-ink-900">{{ $brand->name }}</span>
                    @endif

                    <span class="mt-4 text-sm font-bold text-ink-900">{{ $brand->name }}</span>
                    @if ($brand->country)
                        <span class="mt-1 text-xs text-ink-400">{{ $brand->country }}</span>
                    @endif
                    <span class="tabular mt-3 text-xs text-ink-500">
                        {{ __('ui.results_count', ['count' => $brand->products_count]) }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>
@endsection
