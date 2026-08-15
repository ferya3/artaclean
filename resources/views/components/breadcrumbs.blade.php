@props(['items' => []])

@if (count($items) > 1)
    <nav aria-label="{{ __('ui.breadcrumb') }}" {{ $attributes->merge(['class' => 'py-4']) }}>
        <ol class="no-scrollbar flex items-center gap-1.5 overflow-x-auto text-xs text-ink-500">
            @foreach ($items as $index => $item)
                <li class="flex shrink-0 items-center gap-1.5">
                    @if ($index < count($items) - 1 && ! empty($item['url']))
                        <a href="{{ $item['url'] }}" class="whitespace-nowrap hover:text-ink-900">{{ $item['title'] }}</a>
                        <x-ui-icon name="arrow-left" class="size-3 text-ink-300 flip-rtl" />
                    @else
                        <span class="whitespace-nowrap font-medium text-ink-900" aria-current="page">{{ $item['title'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
