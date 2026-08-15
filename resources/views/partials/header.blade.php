@php
    $navigation = app(App\Services\NavigationService::class);
    $navCategories = $navigation->categories();
    $navEnvironments = $navigation->environments();
@endphp

<header x-data="{ mobile: false, mega: null }" class="sticky top-0 z-50 border-b border-ink-100 bg-white/95 backdrop-blur">
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
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5">
            <span class="grid size-9 place-items-center rounded-lg bg-ink-900 text-sm font-black text-white">A</span>
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
            <a href="{{ route('products.index') }}" class="btn-ghost btn-sm hidden sm:inline-flex" aria-label="{{ __('ui.search_placeholder') }}">
                <x-ui-icon name="search" class="size-4" />
            </a>

            <a href="{{ route('contact') }}" class="btn-primary btn-sm hidden sm:inline-flex">
                {{ __('ui.free_consultation') }}
            </a>

            <button type="button"
                    class="btn-ghost btn-sm lg:hidden"
                    @click="mobile = !mobile"
                    :aria-expanded="mobile"
                    aria-label="{{ __('nav.open_menu') }}">
                <x-ui-icon name="menu" class="size-5" x-show="!mobile" />
                <x-ui-icon name="close" class="size-5" x-cloak x-show="mobile" />
            </button>
        </div>
    </div>

    {{-- Mobile drawer --}}
    <div x-cloak x-show="mobile" x-transition.opacity class="border-t border-ink-100 bg-white lg:hidden">
        <div class="container-page space-y-1 py-4">
            <p class="eyebrow pt-2 pb-1">{{ __('nav.categories') }}</p>
            @foreach ($navCategories as $category)
                <a href="{{ $category->url() }}" class="block rounded-lg px-3 py-2.5 text-sm text-ink-700 hover:bg-ink-50">
                    {{ $category->name }}
                </a>
            @endforeach

            <p class="eyebrow pt-4 pb-1">{{ __('nav.environments') }}</p>
            @foreach ($navEnvironments as $environment)
                <a href="{{ $environment->url() }}" class="block rounded-lg px-3 py-2.5 text-sm text-ink-700 hover:bg-ink-50">
                    {{ $environment->name }}
                </a>
            @endforeach

            <div class="grid gap-1 pt-4">
                <a href="{{ route('selector') }}" class="block rounded-lg px-3 py-2.5 text-sm font-semibold text-brand-700 hover:bg-brand-50">{{ __('nav.selector') }}</a>
                <a href="{{ route('rental') }}" class="block rounded-lg px-3 py-2.5 text-sm text-ink-700 hover:bg-ink-50">{{ __('nav.rental') }}</a>
                <a href="{{ route('brands.index') }}" class="block rounded-lg px-3 py-2.5 text-sm text-ink-700 hover:bg-ink-50">{{ __('nav.brands') }}</a>
                <a href="{{ route('downloads.index') }}" class="block rounded-lg px-3 py-2.5 text-sm text-ink-700 hover:bg-ink-50">{{ __('nav.downloads') }}</a>
                <a href="{{ route('blog.index') }}" class="block rounded-lg px-3 py-2.5 text-sm text-ink-700 hover:bg-ink-50">{{ __('nav.blog') }}</a>
                <a href="{{ route('contact') }}" class="block rounded-lg px-3 py-2.5 text-sm text-ink-700 hover:bg-ink-50">{{ __('nav.contact') }}</a>
            </div>

            <a href="{{ route('contact') }}" class="btn-primary mt-4 w-full">{{ __('ui.free_consultation') }}</a>
        </div>
    </div>
</header>
