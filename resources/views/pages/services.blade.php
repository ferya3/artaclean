@extends('layouts.app')

@section('content')
    @include('partials.page-header', [
        'eyebrow' => __('nav.services'),
        'title' => __('seo.services_title'),
        'subtitle' => __('seo.services_description'),
        'breadcrumbs' => [
            ['title' => __('nav.home'), 'url' => route('home')],
            ['title' => __('nav.services'), 'url' => route('services.index')],
        ],
    ])

    <div class="container-page py-12">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $service)
                <a href="{{ $service->url() }}" class="card-hover tick-frame group flex h-full flex-col p-6">
                    <span class="grid size-12 place-items-center rounded-xl bg-brand-50 text-brand-700
                                 transition-[background-color,color] duration-300 group-hover:bg-brand-600 group-hover:text-white">
                        <x-ui-icon :name="$service->icon ?? 'wrench'" class="size-6" />
                    </span>

                    <h2 class="mt-5 text-base font-bold text-ink-900 transition-colors group-hover:text-brand-700">
                        {{ $service->name }}
                    </h2>

                    @if ($service->short_description)
                        <p class="mt-2 text-sm leading-6 text-ink-500">{{ $service->short_description }}</p>
                    @endif

                    <span class="mt-auto flex items-center gap-2 pt-5 text-sm font-semibold text-brand-700">
                        {{ __('ui.view_details') }}
                        <x-ui-icon name="arrow-left" class="size-4 transition-transform duration-300 group-hover:-translate-x-1 flip-rtl rtl:group-hover:translate-x-1" />
                    </span>
                </a>
            @endforeach
        </div>

        {{-- The one thing every service page eventually asks the visitor to do. --}}
        <div class="mt-10 flex flex-col items-start justify-between gap-5 rounded-[var(--radius-card)] bg-ink-900 p-7 sm:flex-row sm:items-center sm:p-9">
            <div>
                <p class="text-lg font-black text-white">{{ __('services.cta_title') }}</p>
                <p class="mt-2 max-w-xl text-sm leading-7 text-ink-300">{{ __('services.cta_body') }}</p>
            </div>
            <a href="{{ route('contact') }}" class="btn-accent btn-lg w-full shrink-0 sm:w-auto">
                {{ __('ui.free_consultation') }}
                <x-ui-icon name="arrow-left" class="size-4 flip-rtl" />
            </a>
        </div>
    </div>
@endsection
