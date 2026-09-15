@extends('layouts.app')

@section('content')
    @include('partials.page-header', [
        'eyebrow' => __('nav.knowledge'),
        'title' => __('seo.knowledge_title'),
        'subtitle' => __('seo.knowledge_description'),
        'breadcrumbs' => [
            ['title' => __('nav.home'), 'url' => route('home')],
            ['title' => __('nav.knowledge'), 'url' => route('knowledge')],
        ],
    ])

    <div class="container-page py-12">
        @if ($shelves->isEmpty())
            <x-empty-state :title="__('ui.no_results')" icon="document" />
        @else
            <div class="space-y-14">
                @foreach ($shelves as $type => $articles)
                    <section>
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-3">
                            <h2 class="text-xl font-black text-ink-900">{{ __('enums.knowledge_type.'.$type) }}</h2>
                            <span class="tabular text-xs text-ink-400">{{ $articles->count() }}</span>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($articles as $article)
                                <x-article-card :article="$article" />
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif

        {{-- The knowledge hub's job is to end in a machine, not in another article. --}}
        <div class="mt-14 flex flex-col items-start justify-between gap-5 rounded-[var(--radius-card)] border border-ink-100 bg-ink-50 p-7 sm:flex-row sm:items-center">
            <div>
                <p class="text-base font-bold text-ink-900">{{ __('advisor.title') }}</p>
                <p class="mt-1.5 text-sm leading-7 text-ink-500">{{ __('advisor.subtitle') }}</p>
            </div>
            <a href="{{ route('advisor') }}" class="btn-primary w-full shrink-0 sm:w-auto">
                {{ __('advisor.find') }}
                <x-ui-icon name="arrow-left" class="size-4 flip-rtl" />
            </a>
        </div>
    </div>
@endsection
