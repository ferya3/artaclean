@php
    $locale = app()->getLocale();
    $direction = config("site.locales.{$locale}.dir", 'rtl');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $direction }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f1319">

    @include('partials.seo')

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
{{-- The bottom padding keeps the last rows of every page clear of the phone action bar. --}}
<body class="min-h-screen bg-white pb-16 lg:pb-0">
    <a href="#main"
       class="sr-only focus:not-sr-only focus:absolute focus:top-3 focus:start-3 focus:z-[100] focus:rounded-lg focus:bg-ink-900 focus:px-4 focus:py-2 focus:text-white">
        {{ __('nav.menu') }}
    </a>

    @include('partials.header')

    <main id="main">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    @include('partials.footer')

    {{-- Sticky comparison strip, the phone action bar and the WhatsApp shortcut. --}}
    @livewire('compare-bar')
    @include('partials.mobile-cta')
    @include('partials.whatsapp')

    @stack('scripts')
</body>
</html>
