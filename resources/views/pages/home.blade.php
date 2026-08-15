@extends('layouts.app')

@section('content')

    {{-- ------------------------------------------------------------------ --}}
    {{-- Hero                                                               --}}
    {{-- ------------------------------------------------------------------ --}}
    <section class="relative isolate overflow-hidden bg-ink-950">
        <div class="absolute inset-0 -z-10">
            {{--
                A poster image carries the LCP; the looping factory footage
                fades in on top once it has buffered, so the hero never blocks
                first paint.
            --}}
            <img src="{{ asset('images/hero-poster.svg') }}"
                 alt=""
                 fetchpriority="high"
                 class="size-full object-cover opacity-55">
            <video class="absolute inset-0 size-full object-cover opacity-55"
                   autoplay muted loop playsinline preload="none"
                   poster="{{ asset('images/hero-poster.svg') }}">
                <source src="{{ asset('videos/hero.mp4') }}" type="video/mp4">
            </video>
            <div class="absolute inset-0 bg-linear-to-t from-ink-950 via-ink-950/70 to-ink-950/30"></div>
        </div>

        <div class="container-page py-24 sm:py-32 lg:py-40">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold tracking-[0.16em] text-accent-400 uppercase">
                    {{ __('ui.hero_eyebrow') }}
                </p>

                <h1 class="mt-5 text-3xl leading-tight font-black text-white sm:text-4xl lg:text-5xl lg:leading-[1.15]">
                    {{ __('ui.hero_title') }}
                </h1>

                <p class="mt-6 max-w-2xl text-base leading-8 text-ink-200 sm:text-lg">
                    {{ __('ui.hero_subtitle') }}
                </p>

                <div class="mt-9 flex flex-wrap gap-3">
                    <a href="{{ route('products.index') }}" class="btn-primary">
                        {{ __('ui.view_products') }}
                        <x-ui-icon name="arrow-left" class="size-4 flip-rtl" />
                    </a>
                    <a href="{{ route('contact') }}" class="btn bg-white text-ink-900 hover:bg-ink-100">
                        {{ __('ui.free_consultation') }}
                    </a>
                    <a href="{{ route('downloads.index') }}" class="btn border border-white/25 text-white hover:bg-white/10">
                        <x-ui-icon name="download" class="size-4" />
                        {{ __('ui.download_catalog') }}
                    </a>
                </div>

                <dl class="mt-14 grid max-w-lg grid-cols-3 gap-6 border-t border-white/15 pt-8">
                    <div>
                        <dt class="text-xs text-ink-400">{{ __('ui.hero_stat_products') }}</dt>
                        <dd class="tabular text-2xl font-black text-white">
                            {{ number_format($categories->sum('products_count')) }}+
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ink-400">{{ __('ui.hero_stat_brands') }}</dt>
                        <dd class="tabular text-2xl font-black text-white">{{ $brands->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ink-400">{{ __('ui.hero_stat_support') }}</dt>
                        <dd class="tabular text-2xl font-black text-white">۲۴/۷</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    {{-- ------------------------------------------------------------------ --}}
    {{-- Categories                                                         --}}
    {{-- ------------------------------------------------------------------ --}}
    <section class="section">
        <div class="container-page">
            <x-section-heading :eyebrow="__('nav.categories')"
                               :title="__('ui.categories_title')"
                               :subtitle="__('ui.categories_subtitle')"
                               :href="route('products.index')" />

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($categories as $category)
                    <a href="{{ $category->url() }}"
                       class="card-hover group flex items-center gap-4 p-5">
                        <span class="grid size-14 shrink-0 place-items-center rounded-lg bg-ink-50 text-ink-500 transition-colors group-hover:bg-brand-50 group-hover:text-brand-600">
                            <x-ui-icon name="cube" class="size-6" />
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-bold text-ink-900">{{ $category->name }}</span>
                            <span class="tabular mt-0.5 block text-xs text-ink-400">
                                {{ __('ui.results_count', ['count' => $category->products_count]) }}
                            </span>
                        </span>

                        <x-ui-icon name="arrow-left" class="size-4 shrink-0 text-ink-300 transition-transform group-hover:-translate-x-1 flip-rtl rtl:group-hover:translate-x-1" />
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ------------------------------------------------------------------ --}}
    {{-- Environment picker                                                 --}}
    {{-- ------------------------------------------------------------------ --}}
    <section class="section bg-ink-50">
        <div class="container-page">
            <x-section-heading :eyebrow="__('nav.environments')"
                               :title="__('ui.environments_title')"
                               :subtitle="__('ui.environments_subtitle')"
                               :href="route('environments.index')" />

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($environments as $environment)
                    <a href="{{ $environment->url() }}"
                       class="group relative flex aspect-4/3 flex-col justify-end overflow-hidden rounded-[var(--radius-card)] bg-ink-900 p-5">
                        @if ($environment->image)
                            <img src="{{ Storage::url($environment->image) }}"
                                 alt="{{ $environment->name }}"
                                 loading="lazy"
                                 class="absolute inset-0 size-full object-cover opacity-60 transition-transform duration-500 ease-[var(--ease-out-quart)] group-hover:scale-105">
                        @endif
                        <div class="absolute inset-0 bg-linear-to-t from-ink-950 via-ink-950/40 to-transparent"></div>

                        <div class="relative">
                            <h3 class="text-base font-bold text-white">{{ $environment->name }}</h3>
                            @if ($environment->short_description)
                                <p class="mt-1 line-clamp-2 text-xs leading-5 text-ink-300">{{ $environment->short_description }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ------------------------------------------------------------------ --}}
    {{-- Machine selector teaser                                            --}}
    {{-- ------------------------------------------------------------------ --}}
    <section class="section">
        <div class="container-page">
            <div class="grid items-center gap-10 rounded-2xl bg-ink-900 p-8 lg:grid-cols-2 lg:p-14">
                <div>
                    <p class="text-xs font-semibold tracking-[0.16em] text-accent-400 uppercase">
                        {{ __('nav.selector') }}
                    </p>
                    <h2 class="mt-4 text-2xl font-black text-white sm:text-3xl">{{ __('ui.selector_title') }}</h2>
                    <p class="mt-4 max-w-lg text-base leading-8 text-ink-300">{{ __('ui.selector_subtitle') }}</p>

                    <a href="{{ route('selector') }}" class="btn bg-white text-ink-900 mt-8 hover:bg-ink-100">
                        <x-ui-icon name="calculator" class="size-4" />
                        {{ __('ui.calculate') }}
                    </a>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ([
                        ['icon' => 'document', 'key' => 'specs'],
                        ['icon' => 'scale', 'key' => 'compare'],
                        ['icon' => 'calculator', 'key' => 'selector'],
                        ['icon' => 'wrench', 'key' => 'support'],
                    ] as $feature)
                        <div class="rounded-xl bg-white/5 p-5">
                            <x-ui-icon :name="$feature['icon']" class="size-6 text-accent-400" />
                            <h3 class="mt-3 text-sm font-bold text-white">{{ __('ui.why.'.$feature['key'].'_title') }}</h3>
                            <p class="mt-1.5 text-xs leading-6 text-ink-400">{{ __('ui.why.'.$feature['key'].'_body') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ------------------------------------------------------------------ --}}
    {{-- Featured machines                                                  --}}
    {{-- ------------------------------------------------------------------ --}}
    @if ($featured->isNotEmpty())
        <section class="section pt-0">
            <div class="container-page">
                <x-section-heading :eyebrow="__('ui.featured_title')"
                                   :title="__('ui.featured_title')"
                                   :subtitle="__('ui.featured_subtitle')"
                                   :href="route('products.index')" />

                <div x-data="carousel()" class="relative">
                    <div x-ref="carousel" class="swiper !overflow-visible">
                        <div class="swiper-wrapper">
                            @foreach ($featured as $product)
                                <div class="swiper-slide h-auto">
                                    <x-product-card :product="$product" />
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <button x-ref="prev" type="button"
                            class="btn-outline absolute top-1/2 -start-4 z-10 hidden size-10 -translate-y-1/2 rounded-full !p-0 lg:flex"
                            aria-label="Previous">
                        <x-ui-icon name="arrow-right" class="size-4 flip-rtl" />
                    </button>
                    <button x-ref="next" type="button"
                            class="btn-outline absolute top-1/2 -end-4 z-10 hidden size-10 -translate-y-1/2 rounded-full !p-0 lg:flex"
                            aria-label="Next">
                        <x-ui-icon name="arrow-left" class="size-4 flip-rtl" />
                    </button>
                </div>
            </div>
        </section>
    @endif

    {{-- ------------------------------------------------------------------ --}}
    {{-- Brands                                                             --}}
    {{-- ------------------------------------------------------------------ --}}
    @if ($brands->isNotEmpty())
        <section class="border-y border-ink-100 bg-ink-50 py-12">
            <div class="container-page">
                <p class="mb-8 text-center text-xs font-semibold tracking-[0.14em] text-ink-400 uppercase">
                    {{ __('ui.brands_title') }}
                </p>

                <div class="flex flex-wrap items-center justify-center gap-x-10 gap-y-6">
                    @foreach ($brands as $brand)
                        <a href="{{ $brand->url() }}"
                           class="opacity-60 grayscale transition duration-200 hover:opacity-100 hover:grayscale-0">
                            @if ($brand->logo)
                                <img src="{{ Storage::url($brand->logo) }}" alt="{{ $brand->name }}" loading="lazy" class="h-8 w-auto object-contain">
                            @else
                                <span class="text-sm font-bold text-ink-500">{{ $brand->name }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ------------------------------------------------------------------ --}}
    {{-- Articles                                                           --}}
    {{-- ------------------------------------------------------------------ --}}
    @if ($articles->isNotEmpty())
        <section class="section">
            <div class="container-page">
                <x-section-heading :eyebrow="__('nav.blog')"
                                   :title="__('ui.articles_title')"
                                   :href="route('blog.index')" />

                <div class="grid gap-6 md:grid-cols-3">
                    @foreach ($articles as $article)
                        <x-article-card :article="$article" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ------------------------------------------------------------------ --}}
    {{-- FAQ                                                                --}}
    {{-- ------------------------------------------------------------------ --}}
    @if ($faqs->isNotEmpty())
        <section class="section bg-ink-50 pt-0">
            <div class="container-page max-w-3xl pt-16 sm:pt-20">
                <x-section-heading :title="__('ui.faq_title')" :href="route('faq')" class="!mb-8" />
                <x-faq-list :faqs="$faqs" class="[&_details]:bg-white" />
            </div>
        </section>
    @endif

@endsection
