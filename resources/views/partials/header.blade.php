@php
    $navigation = app(App\Services\NavigationService::class);
    $navCategories = $navigation->categories();
    $navEnvironments = $navigation->environments();
@endphp

{{--
    On the home page the bar is out of the way on arrival and slides in once
    the visitor starts scrolling, so the hero owns the first screen. It is
    `fixed` rather than `sticky` for that reason: a sticky bar still occupies a
    row in the layout even while hidden, which would push the hero down by its
    own height.

    Everywhere else it is pinned from the first paint. Hiding it was only ever
    justified by the hero underneath it, and there is no hero on an inner page —
    only a title sitting against the top edge of the window. Those pages are
    also where search traffic lands, this site being built to be found on
    category and product URLs, so arriving there with no logo, no navigation and
    no phone number was the worst possible first screen to hand a buyer.

    `scrolled` is seeded from the live scroll position on init, so a reload
    partway down a page does not start with the bar missing. It is also forced
    open whenever the mobile drawer is open or focus enters the bar — otherwise
    a keyboard user at the top of the page could tab into an invisible menu.
--}}
<header x-data="{
            mobile: false,
            mega: null,
            pinned: {{ request()->routeIs('home') ? 'false' : 'true' }},
            scrolled: false,
            update() { this.scrolled = window.scrollY > 80 },
        }"
        x-init="update()"
        @scroll.window.passive="update()"
        @focusin="scrolled = true"
        @keydown.escape.window="mobile = false; mega = null"
        :class="(pinned || scrolled || mobile) ? 'translate-y-0 opacity-100' : '-translate-y-full opacity-0'"
        class="fixed inset-x-0 top-0 z-50 border-b border-ink-100 bg-white/95 backdrop-blur
               transition-[transform,opacity] duration-300 ease-[var(--ease-out-quart)]
               motion-reduce:transition-none">
    {{-- Utility bar: phone number and language, out of the way but always there. --}}
    <div class="hidden border-b border-ink-100 bg-ink-50 lg:block">
        <div class="container-page flex h-9 items-center justify-between text-xs text-ink-600">
            <div class="flex items-center gap-5">
                <a href="tel:{{ config('site.phone_e164') }}" class="flex items-center gap-1.5 hover:text-ink-900">
                    <x-ui-icon name="phone" class="size-3.5" />
                    <span class="tabular">{{ config('site.phone') }}</span>
                </a>
                <a href="mailto:{{ config('site.email') }}" class="hover:text-ink-900">{{ config('site.email') }}</a>
            </div>

            <div class="flex items-center gap-4">
                <a href="{{ url('/dealer') }}" class="hover:text-ink-900">{{ __('ui.dealer_login') }}</a>
                <span class="text-ink-300">|</span>
                @foreach (config('site.locales') as $code => $meta)
                    <a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}"
                       class="{{ app()->getLocale() === $code ? 'font-semibold text-ink-900' : 'hover:text-ink-900' }}">
                        {{ $meta['native'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="container-page flex h-16 items-center justify-between gap-4 lg:h-20">
        {{--
            The mark is three raked sweep strokes on a dark tile with an accent
            base — a cleaning pass rendered as a diagram, which sits closer to
            the product than a lettered square did. The strokes are deliberately
            slanted: drawn level they read as a hamburger menu button, which is
            the last thing a logo should be mistaken for.
        --}}
        <a href="{{ route('home') }}" class="group flex shrink-0 items-center gap-2.5">
            <span class="relative grid size-9 place-items-center overflow-hidden rounded-lg bg-ink-900 shadow-[var(--shadow-e1)] transition-transform duration-300 ease-[var(--ease-out-quart)] group-hover:-translate-y-px">
                <svg viewBox="0 0 24 24" class="size-[18px] text-white" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M4.5 17.5 10 6.5M10 17.5 15.5 6.5M15.5 17.5 19.5 9.5" />
                </svg>
                <span class="absolute inset-x-0 bottom-0 h-[3px] bg-accent-400"></span>
            </span>
            <span class="text-lg font-black tracking-tight text-ink-900">{{ config('app.name') }}</span>
        </a>

        <nav class="hidden items-center gap-1 lg:flex" aria-label="{{ __('nav.menu') }}">
            {{-- Mega menu: categories on the left, environments on the right. --}}
            <div class="relative" @mouseenter="mega = 'products'" @mouseleave="mega = null">
                <button type="button"
                        class="btn-ghost btn-sm gap-1"
                        :aria-expanded="mega === 'products'"
                        @click="mega = mega === 'products' ? null : 'products'">
                    {{ __('nav.products') }}
                    <x-ui-icon name="chevron-down" class="size-3.5" />
                </button>

                <div x-cloak
                     x-show="mega === 'products'"
                     x-transition.opacity.duration.150ms
                     class="absolute start-0 top-full w-[46rem] rounded-xl border border-ink-100 bg-white p-6 shadow-[var(--shadow-card-hover)]">
                    <div class="grid grid-cols-2 gap-8">
                        <div>
                            <p class="eyebrow mb-3">{{ __('nav.categories') }}</p>
                            <ul class="space-y-1">
                                @foreach ($navCategories as $category)
                                    <li>
                                        <a href="{{ $category->url() }}"
                                           class="flex items-center justify-between rounded-lg px-3 py-2 text-sm text-ink-700 hover:bg-ink-50 hover:text-ink-900">
                                            <span>{{ $category->name }}</span>
                                            <x-ui-icon name="arrow-left" class="size-3.5 text-ink-300 flip-rtl" />
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div>
                            <p class="eyebrow mb-3">{{ __('nav.environments') }}</p>
                            <ul class="grid grid-cols-2 gap-1">
                                @foreach ($navEnvironments as $environment)
                                    <li>
                                        <a href="{{ $environment->url() }}"
                                           class="block rounded-lg px-3 py-2 text-sm text-ink-700 hover:bg-ink-50 hover:text-ink-900">
                                            {{ $environment->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                            <a href="{{ route('selector') }}"
                               class="mt-4 flex items-center gap-2 rounded-lg bg-brand-50 px-3 py-2.5 text-sm font-semibold text-brand-800 hover:bg-brand-100">
                                <x-ui-icon name="calculator" class="size-4" />
                                {{ __('nav.selector') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <a href="{{ route('rental') }}" class="btn-ghost btn-sm">{{ __('nav.rental') }}</a>
            <a href="{{ route('brands.index') }}" class="btn-ghost btn-sm">{{ __('nav.brands') }}</a>
            <a href="{{ route('downloads.index') }}" class="btn-ghost btn-sm">{{ __('nav.downloads') }}</a>
            <a href="{{ route('blog.index') }}" class="btn-ghost btn-sm">{{ __('nav.blog') }}</a>
            <a href="{{ route('contact') }}" class="btn-ghost btn-sm">{{ __('nav.contact') }}</a>
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ route('products.index') }}" class="btn-ghost btn-icon-sm hidden sm:inline-flex" aria-label="{{ __('ui.search_placeholder') }}">
                <x-ui-icon name="search" class="size-4" />
            </a>

            <a href="{{ route('contact') }}" class="btn-primary btn-sm hidden sm:inline-flex">
                {{ __('ui.free_consultation') }}
            </a>

            {{-- 44px square on touch: the one control every mobile visitor aims at. --}}
            <button type="button"
                    class="btn-ghost btn-icon-sm lg:hidden"
                    @click="mobile = !mobile"
                    :aria-expanded="mobile"
                    aria-controls="mobile-nav"
                    aria-label="{{ __('nav.open_menu') }}">
                <x-ui-icon name="menu" class="size-6" x-show="!mobile" />
                <x-ui-icon name="close" class="size-6" x-cloak x-show="mobile" />
            </button>
        </div>
    </div>

    {{-- Mobile drawer --}}
    <div id="mobile-nav" x-cloak x-show="mobile" x-transition.opacity
         class="max-h-[calc(100dvh-4rem)] overflow-y-auto overscroll-contain border-t border-ink-100 bg-white lg:hidden">
        <div class="container-page space-y-1 py-4">
            <p class="eyebrow pt-2 pb-1">{{ __('nav.categories') }}</p>
            @foreach ($navCategories as $category)
                <a href="{{ $category->url() }}" class="flex min-h-12 items-center rounded-lg px-3 text-base text-ink-700 hover:bg-ink-50">
                    {{ $category->name }}
                </a>
            @endforeach

            <p class="eyebrow pt-4 pb-1">{{ __('nav.environments') }}</p>
            @foreach ($navEnvironments as $environment)
                <a href="{{ $environment->url() }}" class="flex min-h-12 items-center rounded-lg px-3 text-base text-ink-700 hover:bg-ink-50">
                    {{ $environment->name }}
                </a>
            @endforeach

            <div class="grid gap-1 pt-4">
                <a href="{{ route('selector') }}" class="flex min-h-12 items-center rounded-lg px-3 text-base font-semibold text-brand-700 hover:bg-brand-50">{{ __('nav.selector') }}</a>
                <a href="{{ route('rental') }}" class="flex min-h-12 items-center rounded-lg px-3 text-base text-ink-700 hover:bg-ink-50">{{ __('nav.rental') }}</a>
                <a href="{{ route('brands.index') }}" class="flex min-h-12 items-center rounded-lg px-3 text-base text-ink-700 hover:bg-ink-50">{{ __('nav.brands') }}</a>
                <a href="{{ route('downloads.index') }}" class="flex min-h-12 items-center rounded-lg px-3 text-base text-ink-700 hover:bg-ink-50">{{ __('nav.downloads') }}</a>
                <a href="{{ route('blog.index') }}" class="flex min-h-12 items-center rounded-lg px-3 text-base text-ink-700 hover:bg-ink-50">{{ __('nav.blog') }}</a>
                <a href="{{ route('contact') }}" class="flex min-h-12 items-center rounded-lg px-3 text-base text-ink-700 hover:bg-ink-50">{{ __('nav.contact') }}</a>
            </div>

            <a href="{{ route('contact') }}" class="btn-primary mt-4 w-full">{{ __('ui.free_consultation') }}</a>
        </div>
    </div>
</header>
