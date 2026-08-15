@props(['eyebrow' => null, 'title', 'subtitle' => null, 'breadcrumbs' => []])

<section class="border-b border-ink-100 bg-ink-50">
    <div class="container-page pb-12 pt-4">
        <x-breadcrumbs :items="$breadcrumbs" />

        @if ($eyebrow)
            <p class="eyebrow mb-2">{{ $eyebrow }}</p>
        @endif

        <h1 class="max-w-3xl text-3xl font-black tracking-tight text-ink-900 sm:text-4xl">{{ $title }}</h1>

        @if ($subtitle)
            <p class="mt-4 max-w-2xl text-base leading-8 text-ink-500">{{ $subtitle }}</p>
        @endif
    </div>
</section>
