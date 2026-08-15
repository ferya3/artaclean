@php
    $navigation = app(App\Services\NavigationService::class);
@endphp

<footer class="mt-auto border-t border-ink-100 bg-ink-950 text-ink-300">
    <div class="container-page grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-5 lg:py-16">
        <div class="lg:col-span-2">
            <div class="flex items-center gap-2.5">
                <span class="grid size-9 place-items-center rounded-lg bg-white text-sm font-black text-ink-950">A</span>
                <span class="text-lg font-black text-white">{{ config('app.name') }}</span>
            </div>

            <p class="mt-4 max-w-sm text-sm leading-7">{{ __('seo.default_description') }}</p>

            <div class="mt-6 space-y-2.5 text-sm">
                <a href="tel:{{ config('site.phone_e164') }}" class="flex items-center gap-2 hover:text-white">
                    <x-ui-icon name="phone" class="size-4 shrink-0" />
                    <span class="tabular">{{ config('site.phone') }}</span>
                </a>
                <a href="mailto:{{ config('site.email') }}" class="flex items-center gap-2 hover:text-white">
                    <x-ui-icon name="mail" class="size-4 shrink-0" />
                    {{ config('site.email') }}
                </a>
                <p class="flex items-start gap-2">
                    <x-ui-icon name="map-pin" class="mt-0.5 size-4 shrink-0" />
                    {{ config('site.address') }}
                </p>
            </div>
        </div>

        <div>
            <h2 class="mb-4 text-sm font-semibold text-white">{{ __('nav.categories') }}</h2>
            <ul class="space-y-2.5 text-sm">
                @foreach ($navigation->categories()->take(9) as $category)
                    <li><a href="{{ $category->url() }}" class="hover:text-white">{{ $category->name }}</a></li>
                @endforeach
            </ul>
        </div>

        <div>
            <h2 class="mb-4 text-sm font-semibold text-white">{{ __('nav.environments') }}</h2>
            <ul class="space-y-2.5 text-sm">
                @foreach ($navigation->environments()->take(8) as $environment)
                    <li><a href="{{ $environment->url() }}" class="hover:text-white">{{ $environment->name }}</a></li>
                @endforeach
            </ul>
        </div>

        <div>
            <h2 class="mb-4 text-sm font-semibold text-white">{{ __('nav.quick_links') }}</h2>
            <ul class="space-y-2.5 text-sm">
                <li><a href="{{ route('selector') }}" class="hover:text-white">{{ __('nav.selector') }}</a></li>
                <li><a href="{{ route('rental') }}" class="hover:text-white">{{ __('nav.rental') }}</a></li>
                <li><a href="{{ route('brands.index') }}" class="hover:text-white">{{ __('nav.brands') }}</a></li>
                <li><a href="{{ route('downloads.index') }}" class="hover:text-white">{{ __('nav.downloads') }}</a></li>
                <li><a href="{{ route('blog.index') }}" class="hover:text-white">{{ __('nav.blog') }}</a></li>
                <li><a href="{{ route('faq') }}" class="hover:text-white">{{ __('nav.faq') }}</a></li>
                <li><a href="{{ route('about') }}" class="hover:text-white">{{ __('nav.about') }}</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-white">{{ __('nav.contact') }}</a></li>
            </ul>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="container-page flex flex-col items-center justify-between gap-3 py-5 text-xs sm:flex-row">
            <p>© {{ date('Y') }} {{ config('app.name') }} — {{ __('nav.rights') }}</p>

            <div class="flex items-center gap-4">
                @if (config('site.instagram'))
                    <a href="{{ config('site.instagram') }}" rel="noopener noreferrer" target="_blank" class="hover:text-white">Instagram</a>
                @endif
                <a href="{{ route('sitemap') }}" class="hover:text-white">Sitemap</a>
                <a href="{{ url('/dealer') }}" class="hover:text-white">{{ __('ui.dealer_login') }}</a>
            </div>
        </div>
    </div>
</footer>
