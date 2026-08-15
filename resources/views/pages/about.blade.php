@extends('layouts.app')

@section('content')
    @include('partials.page-header', [
        'title' => __('nav.about'),
        'subtitle' => __('seo.default_description'),
        'breadcrumbs' => [
            ['title' => __('nav.home'), 'url' => route('home')],
            ['title' => __('nav.about'), 'url' => route('about')],
        ],
    ])

    <div class="container-page max-w-4xl py-12">
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach (['specs', 'compare', 'selector', 'support'] as $key)
                <div class="card p-6">
                    <h2 class="text-base font-bold text-ink-900">{{ __('ui.why.'.$key.'_title') }}</h2>
                    <p class="mt-2 text-sm leading-7 text-ink-500">{{ __('ui.why.'.$key.'_body') }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-10 rounded-[var(--radius-card)] bg-ink-900 p-8 text-center">
            <h2 class="text-lg font-bold text-white">{{ __('contact.title') }}</h2>
            <p class="mt-2 text-sm text-ink-300">{{ __('contact.subtitle') }}</p>
            <a href="{{ route('contact') }}" class="btn bg-white text-ink-900 mt-6 hover:bg-ink-100">
                {{ __('ui.free_consultation') }}
            </a>
        </div>
    </div>
@endsection
