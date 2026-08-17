@extends('layouts.app')

@section('content')
    <div class="container-page">
        <x-breadcrumbs :items="$breadcrumbs" />
    </div>

    <div class="container-page pb-16">
        {{--
            `min-w-0` on the columns is load-bearing, not tidying.

            A grid item defaults to `min-width: auto`, so it refuses to shrink
            below the min-content width of what is inside it. The gallery's
            Swiper then measures that already-blown-out column, writes the
            figure back as an inline slide width, and the page settles at
            around 1,450px on a 390px phone — the whole product page rendered in
            a strip beside a field of empty white, which in RTL is the side the
            document scrolls to.

            Letting the columns shrink to zero breaks the loop at its source:
            Swiper then measures the real 358px and sizes its slides to match.
        --}}
        <div class="grid gap-10 lg:grid-cols-12 lg:gap-14">

            {{-- ---------------------------------------------------------- --}}
            {{-- Gallery                                                    --}}
            {{-- ---------------------------------------------------------- --}}
            <div class="min-w-0 lg:col-span-7">
                @php
                    $gallery = $product->galleryImages;
                    $frames = $product->frames360;
                @endphp

                <div x-data="{ tab: 'gallery' }">
                    @if ($frames->isNotEmpty() || $product->videos->isNotEmpty())
                        <div class="mb-4 flex gap-1 rounded-lg bg-ink-50 p-1">
                            <button type="button" class="btn-sm flex-1 rounded-md font-semibold"
                                    :class="tab === 'gallery' ? 'bg-white text-ink-900 shadow-sm' : 'text-ink-500'"
                                    @click="tab = 'gallery'">
                                {{ __('ui.product.gallery') }}
                            </button>

                            @if ($frames->isNotEmpty())
                                <button type="button" class="btn-sm flex-1 rounded-md font-semibold"
                                        :class="tab === '360' ? 'bg-white text-ink-900 shadow-sm' : 'text-ink-500'"
                                        @click="tab = '360'">
                                    {{ __('ui.product.view_360') }}
                                </button>
                            @endif

                            @if ($product->videos->isNotEmpty())
                                <button type="button" class="btn-sm flex-1 rounded-md font-semibold"
                                        :class="tab === 'video' ? 'bg-white text-ink-900 shadow-sm' : 'text-ink-500'"
                                        @click="tab = 'video'">
                                    {{ __('ui.product.video') }}
                                </button>
                            @endif
                        </div>
                    @endif

                    {{-- Main gallery --}}
                    <div x-show="tab === 'gallery'" x-data="productGallery()" class="relative">
                        <div x-ref="main" class="swiper overflow-hidden rounded-[var(--radius-card)] border border-ink-100 bg-white">
                            <div class="swiper-wrapper">
                                @forelse ($gallery as $image)
                                    <div class="swiper-slide">
                                        <div class="aspect-4/3">
                                            <img src="{{ $image->url() }}"
                                                 alt="{{ $image->alt ?? $product->name }}"
                                                 @class(['size-full object-contain p-6'])
                                                 fetchpriority="{{ $loop->first ? 'high' : 'auto' }}"
                                                 loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                                        </div>
                                    </div>
                                @empty
                                    {{--
                                        Same treatment as the card that linked
                                        here: the machine class this product
                                        actually is, not the one generic
                                        scrubber. Following a vacuum's
                                        silhouette on the catalog through to a
                                        scrubber on its own page is the version
                                        of this that reads as a bug.
                                    --}}
                                    <div class="swiper-slide">
                                        <div class="relative grid aspect-4/3 place-items-center">
                                            <span class="absolute inset-0 blueprint-ink opacity-60"></span>
                                            <x-machine-icon :slug="$product->category?->slug"
                                                            class="relative size-28 text-ink-300 sm:size-36" />
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        @if ($gallery->count() > 1)
                            <button x-ref="prev" type="button"
                                    class="btn-outline absolute top-1/2 start-3 z-10 size-9 -translate-y-1/2 rounded-full !p-0"
                                    aria-label="Previous">
                                <x-ui-icon name="arrow-right" class="size-4 flip-rtl" />
                            </button>
                            <button x-ref="next" type="button"
                                    class="btn-outline absolute top-1/2 end-3 z-10 size-9 -translate-y-1/2 rounded-full !p-0"
                                    aria-label="Next">
                                <x-ui-icon name="arrow-left" class="size-4 flip-rtl" />
                            </button>
                        @endif

                        <div x-ref="thumbs" class="swiper mt-3 {{ $gallery->count() > 1 ? '' : 'hidden' }}">
                            <div class="swiper-wrapper">
                                @foreach ($gallery as $image)
                                    <div class="swiper-slide cursor-pointer rounded-lg border border-ink-100 p-1 [&.swiper-slide-thumb-active]:border-brand-500">
                                        <img src="{{ $image->url() }}" alt="" loading="lazy" class="aspect-square w-full object-contain">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- 360° viewer --}}
                    @if ($frames->isNotEmpty())
                        <div x-cloak x-show="tab === '360'"
                             x-data="viewer360({{ $frames->count() }})"
                             class="relative select-none overflow-hidden rounded-[var(--radius-card)] border border-ink-100 bg-white"
                             @pointerdown.prevent="start($event)"
                             @pointermove="move($event)"
                             @pointerup.window="stop()"
                             @pointerleave="stop()">
                            <div class="aspect-4/3">
                                @foreach ($frames as $frame)
                                    <img src="{{ $frame->url() }}"
                                         alt=""
                                         loading="lazy"
                                         x-show="frame === {{ $loop->index }}"
                                         class="size-full object-contain p-6">
                                @endforeach
                            </div>

                            <p class="pointer-events-none absolute inset-x-0 bottom-4 flex items-center justify-center gap-2 text-xs text-ink-400">
                                <x-ui-icon name="rotate" class="size-4" />
                                {{ __('ui.product.view_360') }}
                            </p>
                        </div>
                    @endif

                    {{-- Video --}}
                    @if ($product->videos->isNotEmpty())
                        <div x-cloak x-show="tab === 'video'" class="space-y-4">
                            @foreach ($product->videos as $video)
                                <div x-data="lazyVideo('{{ $video->embedUrl() }}')"
                                     class="relative aspect-video overflow-hidden rounded-[var(--radius-card)] border border-ink-100 bg-ink-900">
                                    <template x-if="!loaded">
                                        <button type="button" @click="load()" class="group absolute inset-0 grid place-items-center">
                                            @if ($video->thumbnailUrl())
                                                <img src="{{ $video->thumbnailUrl() }}" alt="" loading="lazy" class="absolute inset-0 size-full object-cover opacity-70">
                                            @endif
                                            <span class="relative grid size-16 place-items-center rounded-full bg-white/90 text-ink-900 transition-transform group-hover:scale-110">
                                                <x-ui-icon name="play" class="size-6 ps-1" />
                                            </span>
                                        </button>
                                    </template>

                                    <template x-if="loaded">
                                        <iframe :src="embedUrl" class="size-full" allowfullscreen
                                                title="{{ $video->title ?? $product->name }}"></iframe>
                                    </template>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- ---------------------------------------------------------- --}}
            {{-- Buy box                                                    --}}
            {{-- ---------------------------------------------------------- --}}
            <div class="min-w-0 lg:col-span-5">
                @if ($product->brand)
                    <a href="{{ $product->brand->url() }}" class="text-sm font-semibold text-brand-600 hover:text-brand-800">
                        {{ $product->brand->name }}
                    </a>
                @endif

                <h1 class="mt-2 text-2xl leading-tight font-black text-ink-900 sm:text-3xl">{{ $product->name }}</h1>

                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-ink-500">
                    @if ($product->model_code)
                        <span>{{ __('ui.product.model') }}: <b class="text-ink-700">{{ $product->model_code }}</b></span>
                    @endif
                    @if ($product->sku)
                        <span>{{ __('ui.product.sku') }}: <b class="text-ink-700">{{ $product->sku }}</b></span>
                    @endif
                    <span class="inline-flex items-center gap-1">
                        <span @class([
                            'size-1.5 rounded-full',
                            'bg-emerald-500' => $product->stock_status->value === 'in_stock',
                            'bg-amber-500' => $product->stock_status->value === 'on_order',
                            'bg-red-500' => in_array($product->stock_status->value, ['out_of_stock', 'discontinued']),
                        ])></span>
                        {{ $product->stock_status->getLabel() }}
                    </span>
                </div>

                @if ($product->short_description)
                    <p class="mt-5 text-sm leading-7 text-ink-600">{{ $product->short_description }}</p>
                @endif

                {{-- Key specs strip: the numbers that decide the purchase. --}}
                @php $keySpecs = $product->keySpecs(); @endphp
                @if ($keySpecs->isNotEmpty())
                    <dl class="mt-6 grid grid-cols-2 gap-px overflow-hidden rounded-[var(--radius-card)] border border-ink-100 bg-ink-100 sm:grid-cols-3">
                        @foreach ($keySpecs->take(6) as $spec)
                            <div class="bg-white px-4 py-3.5">
                                <dt class="text-[11px] leading-4 text-ink-400">{{ $spec['label'] }}</dt>
                                <dd class="tabular mt-1 text-sm font-bold text-ink-900">{{ $spec['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                {{-- Price / quote --}}
                <div class="mt-6 rounded-[var(--radius-card)] border border-ink-200 bg-ink-50 p-5">
                    @if ($product->showsPrice())
                        <p class="tabular text-2xl font-black text-ink-900">
                            {{ number_format((float) $product->price) }}
                            <span class="text-sm font-medium text-ink-500">ریال</span>
                        </p>
                    @else
                        <p class="text-base font-bold text-ink-900">{{ __('ui.price_on_request') }}</p>
                        <p class="mt-1 text-xs leading-5 text-ink-500">{{ __('ui.price_note') }}</p>
                    @endif

                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        <a href="#quote" class="btn-primary">{{ __('ui.request_price') }}</a>
                        <a href="https://wa.me/{{ config('site.whatsapp') }}?text={{ rawurlencode($product->name.' — '.$product->url()) }}"
                           target="_blank" rel="noopener noreferrer" class="btn-outline">
                            <x-ui-icon name="whatsapp" class="size-4 text-[#25D366]" />
                            {{ __('ui.product.ask_expert') }}
                        </a>
                    </div>

                    <div class="mt-3 flex items-center justify-between border-t border-ink-200 pt-3">
                        <livewire:compare-button :product-id="$product->id" />
                        <span class="flex items-center gap-1.5 text-xs text-ink-500">
                            <x-ui-icon name="shield" class="size-4" />
                            {{ $product->warranty_months }} {{ __('ui.product.months') }} {{ __('ui.product.warranty') }}
                        </span>
                    </div>
                </div>

                {{-- Rental --}}
                @if ($product->is_rentable && $product->rentalPlans->isNotEmpty())
                    <div class="mt-4 rounded-[var(--radius-card)] border border-accent-400 bg-accent-400/10 p-5">
                        <p class="text-sm font-bold text-ink-900">{{ __('ui.product.rental_available') }}</p>

                        <ul class="mt-3 space-y-2 text-sm">
                            @foreach ($product->rentalPlans as $plan)
                                <li class="flex items-center justify-between gap-3">
                                    <span class="text-ink-700">{{ $plan->period->getLabel() }}</span>
                                    <span class="tabular font-semibold text-ink-900">
                                        @if ($plan->showsPrice())
                                            {{ number_format((float) $plan->price) }} ریال
                                        @else
                                            {{ __('ui.price_on_request') }}
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>

                        <a href="#rental-quote" class="btn-dark btn-sm mt-4 w-full">{{ __('ui.request_rental') }}</a>
                    </div>
                @endif

                {{-- Highlights --}}
                @php $highlights = $product->highlightList(); @endphp
                @if ($highlights !== [])
                    <div class="mt-6">
                        <h2 class="mb-3 text-sm font-bold text-ink-900">{{ __('ui.product.highlights') }}</h2>
                        <ul class="space-y-2">
                            @foreach ($highlights as $highlight)
                                <li class="flex items-start gap-2 text-sm leading-6 text-ink-600">
                                    <x-ui-icon name="check" class="mt-1 size-4 shrink-0 text-brand-600" />
                                    {{ $highlight }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Suitable environments --}}
                @if ($product->environments->isNotEmpty())
                    <div class="mt-6">
                        <h2 class="mb-3 text-sm font-bold text-ink-900">{{ __('ui.product.suitable_for') }}</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($product->environments as $environment)
                                <a href="{{ $environment->url() }}" class="chip hover:border-ink-900">{{ $environment->name }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ---------------------------------------------------------------- --}}
    {{-- Specs, downloads, description                                    --}}
    {{-- ---------------------------------------------------------------- --}}
    <section class="border-t border-ink-100 bg-ink-50 py-16">
        <div class="container-page grid gap-10 lg:grid-cols-3">
            <div class="min-w-0 lg:col-span-2">
                <h2 class="mb-6 text-xl font-bold text-ink-900">{{ __('ui.product.full_specs') }}</h2>

                <div class="overflow-hidden rounded-[var(--radius-card)] border border-ink-100 bg-white">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-ink-100">
                            @foreach ($keySpecs as $spec)
                                <tr>
                                    <th scope="row" class="w-1/2 px-5 py-3 text-start font-medium text-ink-500">{{ $spec['label'] }}</th>
                                    <td class="px-5 py-3 font-semibold text-ink-900">{{ $spec['value'] }}</td>
                                </tr>
                            @endforeach

                            <tr>
                                <th scope="row" class="px-5 py-3 text-start font-medium text-ink-500">{{ __('specs.power_source') }}</th>
                                <td class="px-5 py-3 font-semibold text-ink-900">{{ $product->power_source->getLabel() }}</td>
                            </tr>

                            @if ($product->operator_type)
                                <tr>
                                    <th scope="row" class="px-5 py-3 text-start font-medium text-ink-500">{{ __('specs.operator_type') }}</th>
                                    <td class="px-5 py-3 font-semibold text-ink-900">{{ $product->operator_type->getLabel() }}</td>
                                </tr>
                            @endif

                            @foreach ($product->specs as $spec)
                                <tr>
                                    <th scope="row" class="px-5 py-3 text-start font-medium text-ink-500">{{ $spec->label }}</th>
                                    <td class="px-5 py-3 font-semibold text-ink-900">{{ $spec->displayValue() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($product->description)
                    <h2 class="mt-12 mb-4 text-xl font-bold text-ink-900">{{ __('ui.product.description') }}</h2>
                    <div class="prose-article rounded-[var(--radius-card)] border border-ink-100 bg-white p-6">
                        {!! $product->description !!}
                    </div>
                @endif

                @if ($product->faqs->isNotEmpty())
                    <div class="mt-12">
                        <x-faq-list :faqs="$product->faqs" :title="__('ui.product.faq')" class="[&_details]:bg-white" />
                    </div>
                @endif
            </div>

            {{-- Downloads --}}
            <aside>
                <h2 class="mb-6 text-xl font-bold text-ink-900">{{ __('ui.product.downloads') }}</h2>

                @if ($product->downloads->isNotEmpty())
                    <ul class="space-y-2">
                        @foreach ($product->downloads as $download)
                            <li>
                                <a href="{{ $download->url() }}"
                                   class="card-hover flex items-center gap-3 p-4">
                                    <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-ink-50 text-ink-500">
                                        <x-ui-icon name="download" class="size-4" />
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold text-ink-900">{{ $download->title }}</span>
                                        <span class="tabular block text-xs text-ink-400">
                                            {{ $download->type->getLabel() }}
                                            @if ($download->humanSize()) · {{ $download->humanSize() }} @endif
                                        </span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-ink-500">{{ __('ui.product.no_downloads') }}</p>
                @endif

                @if ($product->blogs->isNotEmpty())
                    <h2 class="mt-10 mb-4 text-xl font-bold text-ink-900">{{ __('ui.product.related_articles') }}</h2>
                    <ul class="space-y-2">
                        @foreach ($product->blogs as $article)
                            <li>
                                <a href="{{ $article->url() }}" class="card-hover block p-4 text-sm font-semibold text-ink-900 hover:text-brand-700">
                                    {{ $article->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </aside>
        </div>
    </section>

    {{-- ---------------------------------------------------------------- --}}
    {{-- Quote forms                                                      --}}
    {{-- ---------------------------------------------------------------- --}}
    <section id="quote" class="section">
        <div class="container-page max-w-3xl">
            <livewire:quote-request-form :product-id="$product->id" />
        </div>
    </section>

    @if ($product->is_rentable)
        <section id="rental-quote" class="section bg-ink-50 pt-0">
            <div class="container-page max-w-3xl pt-16">
                <livewire:quote-request-form :product-id="$product->id" mode="rental" :key="'rental-'.$product->id" />
            </div>
        </section>
    @endif

    {{-- ---------------------------------------------------------------- --}}
    {{-- Related machines                                                 --}}
    {{-- ---------------------------------------------------------------- --}}
    @if ($related->isNotEmpty())
        <section class="section pt-0">
            <div class="container-page">
                <x-section-heading :title="__('ui.product.related')" :href="$product->category->url()" />

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($related as $item)
                        <x-product-card :product="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
