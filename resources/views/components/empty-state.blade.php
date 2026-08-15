@props(['title', 'body' => null, 'href' => null, 'linkLabel' => null, 'icon' => 'cube'])

<div {{ $attributes->merge(['class' => 'rounded-[var(--radius-card)] border border-dashed border-ink-200 bg-ink-50 px-6 py-16 text-center']) }}>
    <x-ui-icon :name="$icon" class="mx-auto size-10 text-ink-300" />
    <h3 class="mt-4 text-base font-bold text-ink-900">{{ $title }}</h3>

    @if ($body)
        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-ink-500">{{ $body }}</p>
    @endif

    @if ($href)
        <a href="{{ $href }}" class="btn-primary btn-sm mt-6">{{ $linkLabel }}</a>
    @endif
</div>
