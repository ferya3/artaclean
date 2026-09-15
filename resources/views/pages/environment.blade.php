@extends('layouts.app')

@section('content')
    <section class="relative isolate overflow-hidden bg-ink-950">
        @if ($environment->image)
            <img src="{{ Storage::url($environment->image) }}" alt="" class="absolute inset-0 -z-10 size-full object-cover opacity-45">
        @endif
        <div class="absolute inset-0 -z-10 bg-linear-to-t from-ink-950 to-ink-950/50"></div>

        <div class="container-page pb-16 pt-4">
            <x-breadcrumbs :items="$breadcrumbs" class="[&_*]:!text-ink-300" />

            <h1 class="mt-4 max-w-3xl text-3xl font-black text-white sm:text-4xl">
                {{ __('seo.environment_title', ['name' => $environment->name]) }}
            </h1>

            @if ($environment->short_description)
                <p class="mt-4 max-w-2xl text-base leading-8 text-ink-200">{{ $environment->short_description }}</p>
            @endif

            @php $challenges = $environment->challengeList(); @endphp
            @if ($challenges !== [])
                <ul class="mt-8 grid max-w-3xl gap-3 sm:grid-cols-2">
                    @foreach ($challenges as $challenge)
                        <li class="flex items-start gap-2 text-sm leading-6 text-ink-200">
                            <x-ui-icon name="check" class="mt-1 size-4 shrink-0 text-accent-400" />
                            {{ $challenge }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>

    {{--
        Problem first, product second.

        Someone who lands on "cleaning a factory" is not browsing a catalogue;
        they have something specific on the floor. Naming the soils this kind
        of site actually has, and putting the machine families that deal with
        each one directly under them, is the whole difference between a
        supplier's website and a buying adviser.
    --}}
    @if ($soils !== [])
        <section class="border-b border-ink-100 bg-ink-50 py-12 sm:py-14">
            <div class="container-page">
                <h2 class="text-xl font-black text-ink-900 sm:text-2xl">{{ __('solutions.problem_title') }}</h2>
                <p class="mt-2 text-sm leading-7 text-ink-500 sm:text-base">{{ __('solutions.problem_subtitle') }}</p>

                <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($soils as $row)
                        <div class="card flex h-full flex-col p-5">
                            <p class="flex items-center gap-2.5 text-sm font-bold text-ink-900">
                                <span class="size-2 rounded-full bg-accent-400"></span>
                                {{ $row['soil']->getLabel() }}
                            </p>

                            <ul class="mt-4 space-y-1">
                                @foreach ($row['categories'] as $category)
                                    <li>
                                        <a href="{{ $category->url() }}"
                                           class="flex items-center justify-between gap-2 rounded-lg px-2.5 py-2 text-sm text-ink-600 hover:bg-ink-50 hover:text-brand-700">
                                            <span>{{ $category->name }}</span>
                                            <x-ui-icon name="arrow-left" class="size-3.5 shrink-0 text-ink-300 flip-rtl" />
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                            <a href="{{ route('products.index', ['soil' => [$row['soil']->value], 'environment' => $environment->slug]) }}"
                               class="mt-auto pt-4 text-xs font-semibold text-brand-700 hover:text-brand-800">
                                {{ __('solutions.see_machines') }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <div class="container-page py-12">
        @if ($products->isNotEmpty())
            <x-section-heading :title="__('ui.featured_title')" />

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @else
            <x-empty-state :title="__('ui.no_results')" :body="__('ui.no_results_hint')"
                           :href="route('contact')" :link-label="__('ui.free_consultation')" />
        @endif
    </div>

    @if ($environment->description)
        <section class="border-t border-ink-100 bg-ink-50 py-14">
            <div class="container-page max-w-4xl">
                <div class="prose-article rounded-[var(--radius-card)] border border-ink-100 bg-white p-8">
                    {!! $environment->description !!}
                </div>
            </div>
        </section>
    @endif

    @if ($environment->faqs->isNotEmpty())
        <section class="section pt-14">
            <div class="container-page max-w-3xl">
                <x-faq-list :faqs="$environment->faqs" :title="__('ui.faq_title')" />
            </div>
        </section>
    @endif
@endsection
