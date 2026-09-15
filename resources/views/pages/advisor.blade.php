@extends('layouts.app')

@section('content')
    @include('partials.page-header', [
        'eyebrow' => __('nav.advisor'),
        'title' => __('seo.advisor_title'),
        'subtitle' => __('advisor.subtitle'),
        'breadcrumbs' => [
            ['title' => __('nav.home'), 'url' => route('home')],
            ['title' => __('nav.advisor'), 'url' => route('advisor')],
        ],
    ])

    <div class="container-page py-12">
        <livewire:machine-advisor />

        {{--
            Two ways past the questions, for the two visitors who do not want
            them: the one who already knows their metreage and wants the
            arithmetic, and the one who knows their building and wants to start
            from that instead.
        --}}
        <div class="mt-10 grid gap-4 lg:grid-cols-3">
            <a href="{{ route('selector') }}" class="card-hover flex items-start gap-4 p-6 lg:col-span-1">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700">
                    <x-ui-icon name="calculator" class="size-5" />
                </span>
                <span>
                    <span class="block text-sm font-bold text-ink-900">{{ __('advisor.exact_title') }}</span>
                    <span class="mt-1.5 block text-sm leading-6 text-ink-500">{{ __('advisor.exact_body') }}</span>
                </span>
            </a>

            <div class="card p-6 lg:col-span-2">
                <p class="text-sm font-bold text-ink-900">{{ __('advisor.by_site_title') }}</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($environments as $environment)
                        <a href="{{ $environment->url() }}" class="chip hover:border-brand-300 hover:text-brand-700">
                            {{ $environment->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
