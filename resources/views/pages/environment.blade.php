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
