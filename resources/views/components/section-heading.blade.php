@props(['eyebrow' => null, 'title', 'subtitle' => null, 'href' => null, 'linkLabel' => null])

<div {{ $attributes->merge(['class' => 'mb-10 flex flex-wrap items-end justify-between gap-4']) }}>
    <div class="max-w-2xl">
        @if ($eyebrow)
            <p class="eyebrow mb-2">{{ $eyebrow }}</p>
        @endif

        <h2 class="text-2xl font-black tracking-tight text-ink-900 sm:text-3xl">{{ $title }}</h2>

        @if ($subtitle)
            <p class="mt-3 text-base leading-7 text-ink-500">{{ $subtitle }}</p>
        @endif
    </div>

    @if ($href)
        <a href="{{ $href }}" class="btn-outline btn-sm shrink-0">
            {{ $linkLabel ?? __('ui.view_all') }}
            <x-ui-icon name="arrow-left" class="size-3.5 flip-rtl" />
        </a>
    @endif
</div>
