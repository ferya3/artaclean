@extends('layouts.app')

@section('content')
    @include('partials.page-header', [
        'title' => __('seo.faq_title'),
        'subtitle' => __('seo.faq_description'),
        'breadcrumbs' => [
            ['title' => __('nav.home'), 'url' => route('home')],
            ['title' => __('nav.faq'), 'url' => route('faq')],
        ],
    ])

    <div class="container-page max-w-3xl py-12">
        @if ($faqs->isEmpty())
            <x-empty-state :title="__('ui.no_results')" icon="document" />
        @else
            <x-faq-list :faqs="$faqs" />
        @endif

        <div class="mt-12 rounded-[var(--radius-card)] bg-ink-900 p-8 text-center">
            <h2 class="text-lg font-bold text-white">{{ __('contact.title') }}</h2>
            <p class="mt-2 text-sm text-ink-300">{{ __('contact.subtitle') }}</p>
            <a href="{{ route('contact') }}" class="btn bg-white text-ink-900 mt-6 hover:bg-ink-100">
                {{ __('ui.free_consultation') }}
            </a>
        </div>
    </div>
@endsection
