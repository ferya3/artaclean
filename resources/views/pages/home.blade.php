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

            The copy is deliberately down to three things: the heading, one
            slogan and the single action a buyer of this equipment actually
            wants. The figures and the reassurance list that used to sit here
            were the reason the machine never made the first screen on a phone —
            the request was to trade them for the enquiry button, which is the
            better bargain anyway: proof belongs further down the page, the
            action belongs where the eye already is.

            `contents` dissolves the copy column on mobile so its two halves
            become siblings of the gallery in the same flex flow. The gallery
            then slots between the slogan and the button via `order`, without
            moving anything in the source and without a second copy of the
            markup. From `lg:` the column re-forms and the grid takes over.
        --}}
        <div class="container-page flex flex-col gap-9 pt-12 pb-14 sm:gap-10 sm:pt-24 sm:pb-16 lg:grid lg:grid-cols-2 lg:items-center lg:gap-14 lg:pt-32 lg:pb-24">
            <div class="contents lg:block">
                <div class="order-1">
                    {{--
                        Type scale is set for the phone first. Material 3 puts a
                        headline at 28-32px on a handset; this one is short
                        enough to hold 32px without wrapping past two lines.
                    --}}
                    <h1 class="text-[2rem] leading-[1.25] font-black text-white sm:text-[2.75rem] sm:leading-[1.15] lg:text-[3.5rem] lg:leading-[1.1]">
                        {{ __('ui.hero_title') }}
                    </h1>

                    {{-- One line. Deliberately the only sentence in the hero. --}}
                    <p class="mt-4 max-w-lg text-base leading-7 text-ink-200 sm:mt-5 sm:text-xl sm:leading-8">
                        {{ __('ui.hero_slogan') }}
                    </p>
                </div>

                {{--
                    The enquiry, in the space the figures and the trust list used
                    to hold. On a phone it lands under the gallery: the visitor
                    sees the machine, then the way to ask what it costs.
                --}}
                <div class="order-3 lg:mt-10">
                    <a href="{{ route('contact') }}" class="btn-accent btn-lg w-full sm:w-auto">
                        {{ __('ui.request_price') }}
                        <x-ui-icon name="arrow-left" class="size-4 flip-rtl" />
                    </a>
                    <p class="mt-3.5 max-w-sm text-xs leading-5 text-ink-400 sm:text-sm sm:leading-6">
                        {{ __('ui.price_note') }}
                    </p>
                </div>
            </div>

            {{--
                Three machines, one at a time, swiped left and right.

                Each is framed 3:2 on a lit backdrop. This is the object the eye
                lands on first, so it has to be the brightest thing on the
                screen: panelled dark against a dark hero it disappeared, while a
                light panel on a near-black ground reads as a lit product shot —
                which is how every serious manufacturer in this category
                photographs a machine.

                `aspect-3/2` on the images reserves the box before the first one
                decodes, so the hero cannot shift once it lands. Only the first
                slide is eager: the other two are a swipe away, not a paint away.
            --}}
            @php
                $heroSlides = [
                    ['key' => 'ride_on', 'image' => 'hero-machine.svg'],
                    ['key' => 'walk_behind', 'image' => 'hero-machine-2.svg'],
                    ['key' => 'vacuum', 'image' => 'hero-machine-3.svg'],
                ];
            @endphp

            <div class="relative order-2 lg:order-none" x-data="heroGallery()">
                <div class="relative overflow-hidden rounded-2xl bg-white shadow-[0_40px_80px_-24px_rgb(0_0_0/0.75)] ring-1 ring-white/15">
                    <div class="swiper" x-ref="carousel">
                        <div class="swiper-wrapper">
                            @foreach ($heroSlides as $slide)
                                <figure class="swiper-slide relative">
                                    <img src="{{ asset('images/'.$slide['image']) }}"
                                         alt="{{ __('ui.hero_slides.'.$slide['key'].'.alt') }}"
                                         width="1200"
                                         height="800"
                                         @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif
                                         decoding="async"
                                         class="aspect-3/2 w-full object-cover">

                                    {{-- Which machine this is. --}}
                                    <figcaption class="absolute top-3 start-3 rounded-lg bg-ink-950/90 px-3 py-1.5 text-[11px] font-bold text-white backdrop-blur sm:top-5 sm:start-5 sm:text-xs">
                                        {{ __('ui.hero_slides.'.$slide['key'].'.name') }}
                                    </figcaption>

                                    {{--
                                        Its headline figure. A buyer sizes a
                                        machine on productivity or suction before
                                        anything else, so that number earns the
                                        one spot the eye is already resting on.

                                        Kept small on a phone: at full size it
                                        sat across the near wheel and hid the
                                        part of the silhouette that identifies
                                        the machine.
                                    --}}
                                    <div class="absolute bottom-3 start-3 flex items-center gap-2.5 rounded-xl bg-ink-950/95 px-3 py-2 shadow-[0_18px_40px_-12px_rgb(0_0_0/0.7)] ring-1 ring-white/10 backdrop-blur sm:bottom-5 sm:start-5 sm:gap-4 sm:px-5 sm:py-3">
                                        <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-accent-400 text-ink-950 sm:size-10">
                                            <x-ui-icon name="check-circle" class="size-4 sm:size-5" />
                                        </span>
                                        <span>
                                            <span class="tabular block text-base leading-none font-black text-white sm:text-xl">
                                                {{ __('ui.hero_slides.'.$slide['key'].'.figure') }}
                                                <span class="text-xs font-bold text-accent-400 sm:text-sm">
                                                    {{ __('ui.hero_slides.'.$slide['key'].'.unit') }}
                                                </span>
                                            </span>
                                            <span class="mt-1 block text-[10px] leading-4 text-ink-400 sm:text-[11px]">
                                                {{ __('ui.hero_slides.'.$slide['key'].'.metric') }}
                                            </span>
                                        </span>
                                    </div>
                                </figure>
                            @endforeach
                        </div>
                    </div>

                    {{--
                        Arrows from sm: up only — a phone swipes the track, and
                        two more tap targets over the machine would cost more
                        than they give. They sit inside the panel over a light
                        image, so they are dark rather than outlined.
                    --}}
                    <button x-ref="prev" type="button"
                            class="absolute top-1/2 start-3 z-10 hidden size-11 -translate-y-1/2 place-items-center rounded-full bg-ink-950/70 text-white ring-1 ring-white/20 backdrop-blur transition hover:bg-ink-950 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-400 sm:grid"
                            aria-label="{{ __('ui.previous') }}">
                        <x-ui-icon name="arrow-left" class="size-4 flip-rtl" />
                    </button>
                    <button x-ref="next" type="button"
                            class="absolute top-1/2 end-3 z-10 hidden size-11 -translate-y-1/2 place-items-center rounded-full bg-ink-950/70 text-white ring-1 ring-white/20 backdrop-blur transition hover:bg-ink-950 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-400 sm:grid"
                            aria-label="{{ __('ui.next') }}">
                        <x-ui-icon name="arrow-right" class="size-4 flip-rtl" />
                    </button>
                </div>

                {{--
                    The dots live under the panel rather than over the machine.
                    Swiper's own stylesheet is unlayered, so its `position:
                    absolute` and its dark inactive bullets can only be beaten
                    from the element itself — hence the inline declarations.
                --}}
                <div x-ref="pagination"
                     class="mt-5 text-center sm:text-start"
                     style="position: static;
                            --swiper-pagination-color: var(--color-accent-400);
                            --swiper-pagination-bullet-inactive-color: #ffffff;
                            --swiper-pagination-bullet-inactive-opacity: 0.35;
                            --swiper-pagination-bullet-size: 8px;
                            --swiper-pagination-bullet-horizontal-gap: 5px"></div>
            </div>
        </div>
    </section>

    {{-- ------------------------------------------------------------------ --}}
    {{-- Who we are                                                         --}}
    {{-- ------------------------------------------------------------------ --}}
    <section class="border-b border-ink-100 bg-white py-16 sm:py-20 lg:py-24">
        <div class="container-page grid gap-12 lg:grid-cols-12 lg:gap-16">
            {{--
                The figures live in the narrow column rather than under the
                prose. Stacked against hairlines they give that column a height
                to match the text beside it — it used to end at the button and
                leave a third of the section empty — and they read better as a
                vertical list than as three cramped cells.
            --}}
            <div class="lg:col-span-5">
                <p class="eyebrow">{{ __('ui.company.eyebrow') }}</p>
                <h2 class="mt-4 text-2xl leading-tight font-black tracking-tight text-ink-900 sm:text-3xl lg:text-4xl">
                    {{ __('ui.company.title') }}
                </h2>

                <dl class="mt-9 space-y-5 border-t border-ink-200">
                    @foreach (['experience', 'clients', 'coverage'] as $fact)
                        <div class="flex items-baseline gap-4 border-b border-ink-100 pb-5">
                            <dt class="tabular w-36 shrink-0 text-xl leading-tight font-black text-ink-900 sm:w-44 sm:text-2xl">
                                {{ __('ui.company.facts.'.$fact.'_value') }}
                            </dt>
                            <dd class="text-sm leading-6 text-ink-500">
                                {{ __('ui.company.facts.'.$fact.'_label') }}
                            </dd>
                        </div>
                    @endforeach
                </dl>

                <a href="{{ route('about') }}" class="btn-outline btn-sm mt-8 w-full sm:w-auto">
                    {{ __('ui.company.more') }}
                    <x-ui-icon name="arrow-left" class="size-3.5 flip-rtl" />
                </a>
            </div>

            <div class="lg:col-span-7">
                <div class="space-y-5 text-base leading-8 text-ink-600 sm:text-lg sm:leading-9">
                    <p class="text-ink-700">{{ __('ui.company.lead') }}</p>
                    <p>{{ __('ui.company.body') }}</p>
                </div>

                {{--
                    A pull quote closes the column: the sentence the whole
                    section is arguing for, set apart so a skimmer who reads
                    nothing else still leaves with it.
                --}}
                <figure class="mt-9 border-s-2 border-accent-400 ps-5">
                    <blockquote class="text-base leading-8 font-bold text-ink-900 sm:text-lg sm:leading-9">
                        {{ __('ui.company.pullquote') }}
                    </blockquote>
                </figure>
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
                Each tile leads with the machine it sells, drawn on the same
                stage as the product photography. Nine identical cards forced a
                buyer to read nine headings in sequence; a distinct shape lets
                them find the right one at a glance, which is the entire job of
                a category grid. The index number stays as a quiet drawing-sheet
                reference behind it.
            --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories as $category)
                    <a href="{{ $category->url() }}"
                       class="card-hover tick-frame group relative flex flex-col overflow-hidden">
                        <span class="relative block aspect-16/10 overflow-hidden bg-linear-to-b from-ink-50 to-white">
                            {{-- The same soft floor the product cards use, so the two sets sit together. --}}
                            <span class="pointer-events-none absolute inset-x-10 bottom-6 h-8 rounded-[50%] bg-ink-900/8 blur-xl"></span>

                            <img src="{{ $category->illustrationUrl() }}"
                                 alt=""
                                 loading="lazy"
                                 decoding="async"
                                 class="relative size-full object-contain p-5 transition-transform duration-500 ease-[var(--ease-out-quart)] group-hover:scale-[1.05]">

                            <span class="absolute top-4 end-5 tabular text-2xl leading-none font-black text-ink-200/90 transition-colors duration-300 group-hover:text-brand-200">
                                {{ str_pad((string) ($loop->index + 1), 2, '0', STR_PAD_LEFT) }}
                            </span>
                        </span>

                        <span class="flex flex-1 flex-col border-t border-ink-100 p-5">
                            <h3 class="text-base leading-7 font-bold text-ink-900 transition-colors group-hover:text-brand-700">
                                {{ $category->name }}
                            </h3>

                            @if ($category->short_description)
                                <p class="mt-2 line-clamp-2 text-sm leading-6 text-ink-500">{{ $category->short_description }}</p>
                            @endif

                            <span class="mt-auto flex items-center justify-between pt-5">
                                <span class="tabular text-xs font-medium text-ink-400">
                                    {{ __('ui.results_count', ['count' => $category->products_count]) }}
                                </span>
                                <x-ui-icon name="arrow-left"
                                           class="size-4 shrink-0 text-ink-300 transition-[transform,color] duration-300 ease-[var(--ease-out-quart)] group-hover:-translate-x-1 group-hover:text-brand-600 flip-rtl rtl:group-hover:translate-x-1" />
                            </span>
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

                            {{--
                                Centred and large rather than tucked in a
                                corner: at a third of the tile's width the
                                silhouette is what tells a visitor which
                                building this is, so it has to carry the tile
                                rather than decorate it. It warms to the accent
                                on hover, which is the only colour move the
                                whole grid makes.
                            --}}
                            <div class="absolute inset-x-0 top-0 flex h-[62%] items-center justify-center text-brand-100/45
                                        transition-[color,transform] duration-500 ease-[var(--ease-out-quart)]
                                        group-hover:-translate-y-1 group-hover:text-accent-300/75">
                                <x-environment-icon :slug="$environment->slug" class="size-20" />
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
                        <x-ui-icon name="arrow-left" class="size-4 flip-rtl" />
                    </button>
                    <button x-ref="next" type="button"
                            class="btn-outline absolute top-1/2 -end-4 z-10 hidden size-10 -translate-y-1/2 rounded-full !p-0 lg:flex"
                            aria-label="Next">
                        <x-ui-icon name="arrow-right" class="size-4 flip-rtl" />
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
                    A logo wall, not a line of loose words. Each name sits in
                    its own framed tile of the same size, which is what makes a
                    set of wordmarks read as a roster of brands the way real
                    logos would; the frame also gives the row something to
                    respond to on hover.
                --}}
                {{-- Written out rather than interpolated: Tailwind only ships classes it can read in the source. --}}
                @php
                    $brandColumns = match (true) {
                        $brands->count() >= 7 => 'lg:grid-cols-7',
                        $brands->count() === 6 => 'lg:grid-cols-6',
                        $brands->count() === 5 => 'lg:grid-cols-5',
                        default => 'lg:grid-cols-4',
                    };
                @endphp

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 {{ $brandColumns }}">
                    @foreach ($brands as $brand)
                        <a href="{{ $brand->url() }}"
                           class="group flex h-20 items-center justify-center rounded-[var(--radius-card)] border border-ink-100 bg-white px-3
                                  transition-[transform,border-color,box-shadow] duration-300 ease-[var(--ease-out-quart)]
                                  hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-[var(--shadow-e2)]">
                            @if ($brand->logo)
                                <img src="{{ Storage::url($brand->logo) }}" alt="{{ $brand->name }}" loading="lazy"
                                     class="h-8 w-auto object-contain opacity-70 grayscale transition duration-300 group-hover:opacity-100 group-hover:grayscale-0">
                            @else
                                <span class="text-center text-base leading-6 font-black tracking-tight text-ink-500 transition-colors group-hover:text-brand-700">
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
