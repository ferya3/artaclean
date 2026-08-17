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
                 class="size-full object-cover opacity-40">
            <video class="absolute inset-0 size-full object-cover opacity-40"
                   autoplay muted loop playsinline preload="none"
                   poster="{{ asset('images/hero-poster.svg') }}">
                <source src="{{ asset('videos/hero.mp4') }}" type="video/mp4">
            </video>

            {{--
                The panel is designed to stand up with no footage behind it at
                all: a blueprint grid for scale, a cool key light from the top
                of the text column, and a floor gradient to seat the type. Real
                footage lands on top of this rather than being load-bearing.
            --}}
            <div class="absolute inset-0 blueprint"></div>
            <div class="absolute -top-40 -start-32 size-[38rem] glow-brand"></div>
            <div class="absolute -bottom-52 -end-24 size-[34rem] glow-accent"></div>
            <div class="absolute inset-0 bg-linear-to-t from-ink-950 via-ink-950/80 to-ink-950/40"></div>
            <div class="absolute inset-x-0 bottom-0 h-px bg-linear-to-r from-transparent via-white/20 to-transparent"></div>
        </div>

        {{--
            Two columns from lg up, one on a phone.

            The copy stays first in source order everywhere — it carries the H1
            and the LCP text. But on a phone the headline, the sub, three
            stacked buttons, the figures and the trust list together ran past
            the fold, so the machine never appeared on the first screen at all:
            a visitor arriving on the page that is meant to sell equipment saw
            only type on a dark ground.

            `contents` dissolves the copy column on mobile so its two halves
            become siblings of the image in the same flex flow. The image then
            slots between the buttons and the figures via `order`, without
            moving anything in the source and without a second copy of the
            markup. From `lg:` the column re-forms and the grid takes over.
        --}}
        <div class="container-page flex flex-col gap-10 pt-16 pb-16 sm:pt-24 lg:grid lg:grid-cols-2 lg:items-center lg:gap-14 lg:pt-32 lg:pb-24">
            <div class="contents lg:block">
                <div class="order-1">
                <p class="eyebrow-light">{{ __('ui.hero_eyebrow') }}</p>

                <h1 class="mt-6 text-[2rem] leading-[1.2] font-black text-white sm:text-5xl sm:leading-[1.15] lg:text-[3.25rem] lg:leading-[1.12]">
                    {{ __('ui.hero_title') }}
                </h1>

                <p class="mt-7 max-w-xl text-base leading-8 text-ink-300 sm:text-lg sm:leading-9">
                    {{ __('ui.hero_subtitle') }}
                </p>

                {{--
                    Full-width stacked buttons on a phone: a wrapped row of
                    pills leaves half-width targets and ragged edges, and the
                    primary action should not have to be aimed at.
                --}}
                <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <a href="{{ route('products.index') }}" class="btn-accent btn-lg w-full sm:w-auto">
                        {{ __('ui.view_products') }}
                        <x-ui-icon name="arrow-left" class="size-4 flip-rtl" />
                    </a>
                    <a href="{{ route('selector') }}"
                       class="btn btn-lg w-full border border-white/20 bg-white/5 text-white backdrop-blur-sm hover:-translate-y-px hover:border-white/40 hover:bg-white/10 sm:w-auto">
                        <x-ui-icon name="calculator" class="size-4" />
                        {{ __('ui.calculate') }}
                    </a>
                    <a href="{{ route('downloads.index') }}"
                       class="btn btn-lg w-full text-ink-300 hover:bg-white/5 hover:text-white sm:w-auto">
                        <x-ui-icon name="download" class="size-4" />
                        {{ __('ui.download_catalog') }}
                    </a>
                </div>
                </div>

                {{-- Supporting proof: below the machine on a phone, under the copy on desktop. --}}
                <div class="order-3 lg:mt-14">
                {{-- Figures first, reassurance underneath. --}}
                <dl class="grid max-w-xl grid-cols-3 border-t border-white/15 pt-8 [&>*+*]:border-s [&>*+*]:border-white/10 [&>*+*]:ps-6">
                    <div>
                        <dd class="tabular text-3xl leading-none font-black text-white sm:text-4xl">
                            {{ number_format($categories->sum('products_count')) }}<span class="text-accent-400">+</span>
                        </dd>
                        <dt class="mt-2.5 text-xs text-ink-400">{{ __('ui.hero_stat_products') }}</dt>
                    </div>
                    <div class="ps-6">
                        <dd class="tabular text-3xl leading-none font-black text-white sm:text-4xl">{{ $brands->count() }}</dd>
                        <dt class="mt-2.5 text-xs text-ink-400">{{ __('ui.hero_stat_brands') }}</dt>
                    </div>
                    <div class="ps-6">
                        <dd class="tabular text-3xl leading-none font-black text-white sm:text-4xl">۲۴/۷</dd>
                        <dt class="mt-2.5 text-xs text-ink-400">{{ __('ui.hero_stat_support') }}</dt>
                    </div>
                </dl>

                <ul class="mt-8 flex flex-wrap gap-x-7 gap-y-3">
                    @foreach (['warranty', 'parts', 'service'] as $signal)
                        <li class="flex items-center gap-2 text-sm text-ink-300 sm:text-base lg:text-sm">
                            <x-ui-icon name="check-circle" class="size-4 shrink-0 text-accent-400" />
                            {{ __('ui.hero_trust.'.$signal) }}
                        </li>
                    @endforeach
                </ul>
                </div>
            </div>

            {{--
                The machine, framed 3:2 on a lit backdrop.

                This is the object the eye lands on first, so it has to be the
                brightest thing on the screen. Panelled dark against a dark hero
                it disappeared — the type was carrying the whole first
                impression on its own. A light panel on a near-black ground
                reads as a lit product shot, which is exactly how every serious
                manufacturer in this category photographs a machine.

                `aspect-3/2` reserves the box before the image decodes, so the
                hero cannot shift once it lands.
            --}}
            <div class="relative order-2 lg:order-none">
                <figure class="relative">
                    <div class="relative overflow-hidden rounded-2xl bg-white shadow-[0_40px_80px_-24px_rgb(0_0_0/0.75)] ring-1 ring-white/15">
                        <img src="{{ asset('images/hero-machine.svg') }}"
                             alt="{{ __('ui.hero_image_alt') }}"
                             width="1200"
                             height="800"
                             fetchpriority="high"
                             decoding="async"
                             class="aspect-3/2 w-full object-cover">
                    </div>

                    {{--
                        The headline figure, lifted off the panel. A buyer sizes
                        a machine on productivity before anything else, so it
                        earns the one spot the eye is already resting on.
                    --}}
                    <div class="absolute -bottom-5 start-4 flex items-center gap-3 rounded-xl bg-ink-950/95 px-4 py-3 shadow-[0_18px_40px_-12px_rgb(0_0_0/0.7)] ring-1 ring-white/10 backdrop-blur sm:start-6 sm:gap-4 sm:px-5">
                        <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-accent-400 text-ink-950">
                            <x-ui-icon name="check-circle" class="size-5" />
                        </span>
                        <span>
                            <span class="tabular block text-lg leading-none font-black text-white sm:text-xl">
                                ۵٬۰۰۰ <span class="text-sm font-bold text-accent-400">m²/h</span>
                            </span>
                            <span class="mt-1 block text-[11px] leading-4 text-ink-400">
                                {{ __('ui.hero_badge_label') }}
                            </span>
                        </span>
                    </div>
                </figure>

                <figcaption class="mt-10 flex items-center gap-2 text-xs text-ink-400">
                    <span class="h-px w-6 bg-accent-400"></span>
                    {{ __('ui.hero_image_caption') }}
                </figcaption>
            </div>
        </div>
    </section>

    {{-- ------------------------------------------------------------------ --}}
    {{-- Who we are                                                         --}}
    {{-- ------------------------------------------------------------------ --}}
    <section class="border-b border-ink-100 bg-white py-16 sm:py-20 lg:py-24">
        <div class="container-page grid gap-12 lg:grid-cols-12 lg:gap-16">
            <div class="lg:col-span-5">
                <p class="eyebrow">{{ __('ui.company.eyebrow') }}</p>
                <h2 class="mt-4 text-2xl leading-tight font-black tracking-tight text-ink-900 sm:text-3xl lg:text-4xl">
                    {{ __('ui.company.title') }}
                </h2>

                <a href="{{ route('about') }}" class="btn-outline btn-sm mt-8 w-full sm:w-auto">
                    {{ __('ui.company.more') }}
                    <x-ui-icon name="arrow-left" class="size-3.5 flip-rtl" />
                </a>
            </div>

            <div class="lg:col-span-7">
                <div class="space-y-5 text-base leading-8 text-ink-600 sm:text-lg sm:leading-9">
                    <p>{{ __('ui.company.lead') }}</p>
                    <p>{{ __('ui.company.body') }}</p>
                </div>

                <dl class="mt-10 grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-3">
                    @foreach (['experience', 'clients', 'coverage'] as $fact)
                        <div class="border-t border-ink-200 pt-4">
                            <dt class="tabular text-2xl font-black text-ink-900 sm:text-3xl">
                                {{ __('ui.company.facts.'.$fact.'_value') }}
                            </dt>
                            <dd class="mt-1.5 text-sm leading-6 text-ink-500">
                                {{ __('ui.company.facts.'.$fact.'_label') }}
                            </dd>
                        </div>
                    @endforeach
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

            {{--
                Each tile carries the silhouette of the machine it sells. Nine
                identical cards forced a buyer to read nine headings in
                sequence; a distinct shape lets them find the right one at a
                glance, which is the entire job of a category grid. The index
                number stays as a quiet drawing-sheet reference behind it.
            --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories as $category)
                    <a href="{{ $category->url() }}"
                       class="card-hover tick-frame group relative flex flex-col overflow-hidden p-6">
                        <span class="absolute top-5 end-5 tabular text-2xl leading-none font-black text-ink-100 transition-colors duration-300 group-hover:text-brand-100">
                            {{ str_pad((string) ($loop->index + 1), 2, '0', STR_PAD_LEFT) }}
                        </span>

                        <span class="mb-5 grid size-14 place-items-center rounded-xl bg-brand-50 text-brand-700
                                     transition-[background-color,color,transform] duration-300 ease-[var(--ease-out-quart)]
                                     group-hover:-translate-y-0.5 group-hover:bg-brand-600 group-hover:text-white">
                            <x-machine-icon :slug="$category->slug" class="size-8" />
                        </span>

                        <h3 class="pe-12 text-base leading-7 font-bold text-ink-900 transition-colors group-hover:text-brand-700">
                            {{ $category->name }}
                        </h3>

                        @if ($category->short_description)
                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-ink-500">{{ $category->short_description }}</p>
                        @endif

                        <span class="mt-5 flex items-center justify-between border-t border-ink-100 pt-4">
                            <span class="tabular text-xs font-medium text-ink-400">
                                {{ __('ui.results_count', ['count' => $category->products_count]) }}
                            </span>
                            <x-ui-icon name="arrow-left"
                                       class="size-4 shrink-0 text-ink-300 transition-[transform,color] duration-300 ease-[var(--ease-out-quart)] group-hover:-translate-x-1 group-hover:text-brand-600 flip-rtl rtl:group-hover:translate-x-1" />
                        </span>
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
                       class="group relative flex aspect-4/3 flex-col justify-end overflow-hidden rounded-[var(--radius-card)] bg-ink-900 p-5
                              shadow-[var(--shadow-e1)] ring-1 ring-ink-900/5 transition-[transform,box-shadow] duration-300 ease-[var(--ease-out-quart)]
                              hover:-translate-y-0.5 hover:shadow-[var(--shadow-e3)]">
                        @if ($environment->image)
                            <img src="{{ Storage::url($environment->image) }}"
                                 alt="{{ $environment->name }}"
                                 loading="lazy"
                                 class="absolute inset-0 size-full object-cover opacity-60 transition-transform duration-500 ease-[var(--ease-out-quart)] group-hover:scale-105">
                        @else
                            {{--
                                No photograph, so the tile is built rather than
                                filled: a petrol gradient instead of flat black,
                                a blueprint weave for texture, and the site's own
                                silhouette ghosted in behind the label. Eight
                                identical dark rectangles read as broken images;
                                these read as a set.
                            --}}
                            <div class="absolute inset-0 bg-linear-to-br from-brand-800 via-ink-900 to-ink-950"></div>
                            <div class="absolute inset-0 blueprint-fine opacity-60"></div>
                            <div class="absolute -top-16 -end-10 size-56 glow-brand opacity-80 transition-opacity duration-500 group-hover:opacity-100"></div>

                            <div class="absolute top-4 end-5 text-brand-200/35 transition-[color,transform] duration-500 ease-[var(--ease-out-quart)] group-hover:-translate-y-0.5 group-hover:text-brand-200/60">
                                <x-environment-icon :slug="$environment->slug" class="size-14" />
                            </div>
                        @endif

                        <div class="absolute inset-0 bg-linear-to-t from-ink-950 via-ink-950/35 to-transparent"></div>

                        {{-- A hairline that fills with the accent as the tile is hovered. --}}
                        <span class="absolute inset-x-5 bottom-0 h-0.5 origin-left scale-x-0 bg-accent-400 transition-transform duration-500 ease-[var(--ease-out-quart)] group-hover:scale-x-100 rtl:origin-right"></span>

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
            <div class="relative isolate grid items-center gap-10 overflow-hidden rounded-2xl bg-ink-900 p-8 shadow-[var(--shadow-e3)] lg:grid-cols-2 lg:p-14">
                <div class="absolute inset-0 -z-10 blueprint"></div>
                <div class="absolute -top-32 -start-20 -z-10 size-[26rem] glow-brand"></div>
                <div class="absolute inset-x-0 top-0 -z-10 h-px bg-linear-to-r from-transparent via-white/25 to-transparent"></div>

                <div>
                    <p class="eyebrow-light">{{ __('nav.selector') }}</p>
                    <h2 class="mt-5 text-2xl leading-snug font-black text-white sm:text-3xl">{{ __('ui.selector_title') }}</h2>
                    <p class="mt-4 max-w-lg text-base leading-8 text-ink-300">{{ __('ui.selector_subtitle') }}</p>

                    <a href="{{ route('selector') }}" class="btn-accent mt-8">
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
                        <div class="rounded-xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm transition-colors duration-300 hover:border-white/20 hover:bg-white/10">
                            <span class="grid size-10 place-items-center rounded-lg bg-accent-400/10 text-accent-400 ring-1 ring-accent-400/20">
                                <x-ui-icon :name="$feature['icon']" class="size-5" />
                            </span>
                            <h3 class="mt-4 text-sm font-bold text-white">{{ __('ui.why.'.$feature['key'].'_title') }}</h3>
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
                {{-- The eyebrow repeated the title verbatim; the section label belongs here. --}}
                <x-section-heading :eyebrow="__('nav.products')"
                                   :title="__('ui.featured_title')"
                                   :subtitle="__('ui.featured_subtitle')"
                                   :href="route('products.index')" />

                <div x-data="carousel()" class="relative">
                    {{--
                        The track must clip horizontally. Left visible, the
                        wrapper is as wide as all eight slides at once, and that
                        width becomes the document's — on a phone it rendered the
                        entire site in a 390px strip beside 1,500px of blank
                        page. The padding/negative-margin pair gives the card
                        hover lift and its shadow room to breathe vertically
                        without reopening the horizontal axis.
                    --}}
                    <div x-ref="carousel" class="swiper -my-3 py-3">
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
        <section class="border-y border-ink-100 bg-ink-50 py-14">
            <div class="container-page">
                <div class="mb-9 flex items-center gap-5">
                    <div class="h-px flex-1 bg-ink-200"></div>
                    <p class="shrink-0 text-xs font-semibold tracking-[0.14em] text-ink-400 uppercase">
                        {{ __('ui.brands_title') }}
                    </p>
                    <div class="h-px flex-1 bg-ink-200"></div>
                </div>

                {{--
                    Without real logo files this row is wordmarks, so it is set
                    as one: even optical weight, generous tracking, and a single
                    hover that brings the name to full ink.
                --}}
                <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-7">
                    @foreach ($brands as $brand)
                        <a href="{{ $brand->url() }}"
                           class="group grayscale opacity-70 transition duration-300 ease-[var(--ease-out-quart)] hover:opacity-100 hover:grayscale-0">
                            @if ($brand->logo)
                                <img src="{{ Storage::url($brand->logo) }}" alt="{{ $brand->name }}" loading="lazy" class="h-8 w-auto object-contain">
                            @else
                                <span class="text-base font-black tracking-tight text-ink-500 transition-colors group-hover:text-ink-900">
                                    {{ $brand->name }}
                                </span>
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
