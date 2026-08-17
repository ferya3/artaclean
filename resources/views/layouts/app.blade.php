@php
    $locale = app()->getLocale();
    $direction = config("site.locales.{$locale}.dir", 'rtl');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $direction }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- ink-950: the hue the header and the dark panels are actually built on. --}}
    <meta name="theme-color" content="#071417">

    @include('partials.seo')

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="min-h-screen bg-white">
    <a href="#main"
       class="sr-only focus:not-sr-only focus:absolute focus:top-3 focus:start-3 focus:z-[100] focus:rounded-lg focus:bg-ink-900 focus:px-4 focus:py-2 focus:text-white">
        {{ __('nav.menu') }}
    </a>

    @include('partials.header')

    {{--
        The bar is `fixed`, so it is out of the flow and cannot push anything
        down itself. On every page but the home one it is pinned open from the
        first paint, and the content has to start below it rather than under it.

        The two values are the bar's own measured height: one row at 65px until
        `lg`, where the utility row appears and takes it to 118px. They have to
        move with `h-16 lg:h-20` and the `lg:block` on that row.
    --}}
    <main id="main" @class(['pt-[65px] lg:pt-[118px]' => ! request()->routeIs('home')])>
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    @include('partials.footer')

    {{-- Sticky comparison strip and the WhatsApp shortcut sit above everything. --}}
    @livewire('compare-bar')
    @include('partials.whatsapp')

    @stack('scripts')
</body>
</html>
