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
        {{-- The page behind a drawer must not scroll with it. --}}
        x-effect="document.body.classList.toggle('overflow-hidden', mobile)"
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

    <div class="container-page relative flex h-16 items-center justify-between gap-4 lg:h-20">
        {{--
            The menu button opens the bar at the start edge — the right, in
            Persian. That is the edge the drawer travels in from, so the thumb
            that opens it is already on the side the panel arrives from.
        --}}
        <button type="button"
                class="btn-ghost btn-icon-sm lg:hidden"
                @click="mobile = true"
                :aria-expanded="mobile"
                aria-controls="mobile-nav"
                aria-label="{{ __('nav.open_menu') }}">
            <x-ui-icon name="menu" class="size-6" />
        </button>

        {{--
            The mark is three raked sweep strokes on a dark tile with an accent
            base — a cleaning pass rendered as a diagram, which sits closer to
            the product than a lettered square did. The strokes are deliberately
            slanted: drawn level they read as a hamburger menu button, which is
            the last thing a logo should be mistaken for.

            Centred on a phone, and back at the start from `lg:` where the
            navigation needs the middle of the bar. The centring is done with
            physical `left-1/2` rather than a logical offset on purpose: paired
            with a physical translate it lands on the true centre in both
            directions, where a logical pair has to be flipped per direction to
            mean the same thing.
        --}}
        <a href="{{ route('home') }}"
           class="group absolute left-1/2 flex shrink-0 -translate-x-1/2 items-center gap-2.5 lg:static lg:translate-x-0">
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
        </div>
    </div>

    {{--
        The mobile menu is a drawer off the start edge — the right, in Persian —
        rather than a panel unfolding under the bar. It travels on a transform
        with a backdrop fading in behind it, which is what the old version was
        missing: it only crossfaded its opacity, so it arrived fully formed with
        nothing to say where it had come from.

        It is teleported to the body because the header carries a transform at
        all times — that transform is what slides the bar in and out — and a
        transformed ancestor becomes the containing block for `fixed`
        descendants. Left inside the header, a fixed drawer would be positioned
        against the header rather than the viewport, and would ride up with it.

        The travel is written per direction. `translate-x-full` is physical, so
        on its own it would send the panel off the left in an English page and
        off the right in a Persian one; the pair below keeps it leaving by the
        same edge it is anchored to either way.
    --}}
    <template x-teleport="body">
        <div class="lg:hidden">
            <div x-cloak
                 x-show="mobile"
                 x-transition:enter="transition-opacity duration-300 ease-[var(--ease-out-quart)]"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity duration-200 ease-[var(--ease-out-quart)]"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="mobile = false"
                 class="fixed inset-0 z-[55] bg-ink-950/60 backdrop-blur-sm"
                 aria-hidden="true"></div>

            <div id="mobile-nav"
                 x-cloak
                 x-show="mobile"
                 x-transition:enter="transition-transform duration-300 ease-[var(--ease-out-quart)]"
                 x-transition:enter-start="rtl:translate-x-full ltr:-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition-transform duration-200 ease-[var(--ease-out-quart)]"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="rtl:translate-x-full ltr:-translate-x-full"
                 class="fixed inset-y-0 start-0 z-[60] flex w-[86%] max-w-sm flex-col bg-white shadow-[var(--shadow-e3)]"
                 role="dialog"
                 aria-modal="true"
                 aria-label="{{ __('nav.menu') }}">

                <div class="flex h-16 shrink-0 items-center justify-between border-b border-ink-100 px-5">
                    <span class="text-sm font-bold text-ink-900">{{ __('nav.menu') }}</span>
                    <button type="button" @click="mobile = false" class="btn-ghost btn-icon-sm -me-2"
                            aria-label="{{ __('nav.close_menu') }}">
                        <x-ui-icon name="close" class="size-6" />
                    </button>
                </div>

                {{--
                    `$row` counts straight through the headings and all three
                    groups, so the cascade is one run down the list rather than
                    three that each start over. The secondary links lose their
                    wrapping grid for the same reason: a wrapper would animate
                    as a single row and land its six links together.
                --}}
                @php $row = 0; @endphp

                <div class="menu-stagger flex-1 space-y-1 overflow-y-auto overscroll-contain px-5 py-4">
                    <p class="eyebrow pt-2 pb-1" style="--i: {{ $row++ }}">{{ __('nav.categories') }}</p>
                    @foreach ($navCategories as $category)
                        <a href="{{ $category->url() }}" style="--i: {{ $row++ }}"
                           class="flex min-h-12 items-center rounded-lg px-3 text-base text-ink-700 hover:bg-ink-50">
                            {{ $category->name }}
                        </a>
                    @endforeach

                    <p class="eyebrow pt-4 pb-1" style="--i: {{ $row++ }}">{{ __('nav.environments') }}</p>
                    @foreach ($navEnvironments as $environment)
                        <a href="{{ $environment->url() }}" style="--i: {{ $row++ }}"
                           class="flex min-h-12 items-center rounded-lg px-3 text-base text-ink-700 hover:bg-ink-50">
                            {{ $environment->name }}
                        </a>
                    @endforeach

                    {{--
                        Carries the gap the removed wrapper used to hold, and
                        earns it by marking where the machine lists stop and the
                        rest of the site starts. It takes a place in the
                        cascade like any other row.
                    --}}
                    <span class="my-3 block h-px bg-ink-100" style="--i: {{ $row++ }}"></span>

                    <a href="{{ route('selector') }}" style="--i: {{ $row++ }}" class="flex min-h-12 items-center rounded-lg px-3 text-base font-semibold text-brand-700 hover:bg-brand-50">{{ __('nav.selector') }}</a>
                    <a href="{{ route('rental') }}" style="--i: {{ $row++ }}" class="flex min-h-12 items-center rounded-lg px-3 text-base text-ink-700 hover:bg-ink-50">{{ __('nav.rental') }}</a>
                    <a href="{{ route('brands.index') }}" style="--i: {{ $row++ }}" class="flex min-h-12 items-center rounded-lg px-3 text-base text-ink-700 hover:bg-ink-50">{{ __('nav.brands') }}</a>
                    <a href="{{ route('downloads.index') }}" style="--i: {{ $row++ }}" class="flex min-h-12 items-center rounded-lg px-3 text-base text-ink-700 hover:bg-ink-50">{{ __('nav.downloads') }}</a>
                    <a href="{{ route('blog.index') }}" style="--i: {{ $row++ }}" class="flex min-h-12 items-center rounded-lg px-3 text-base text-ink-700 hover:bg-ink-50">{{ __('nav.blog') }}</a>
                    <a href="{{ route('contact') }}" style="--i: {{ $row++ }}" class="flex min-h-12 items-center rounded-lg px-3 text-base text-ink-700 hover:bg-ink-50">{{ __('nav.contact') }}</a>
                </div>

                {{-- The two ways to start a conversation, held out of the scroll. --}}
                <div class="shrink-0 border-t border-ink-100 p-5">
                    <a href="{{ route('contact') }}" class="btn-primary w-full">{{ __('ui.free_consultation') }}</a>
                    <a href="tel:{{ config('site.phone_e164') }}"
                       class="mt-3 flex items-center justify-center gap-2 text-sm font-medium text-ink-600 hover:text-ink-900">
                        <x-ui-icon name="phone" class="size-4" />
                        <span class="tabular">{{ config('site.phone') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </template>
</header>
