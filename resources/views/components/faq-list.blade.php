@props(['faqs', 'title' => null])

@if ($faqs->isNotEmpty())
    <div {{ $attributes->merge(['class' => '']) }}>
        @if ($title)
            <h2 class="mb-6 text-xl font-bold text-ink-900">{{ $title }}</h2>
        @endif

        <div class="divide-y divide-ink-100 rounded-[var(--radius-card)] border border-ink-100">
            @foreach ($faqs as $faq)
                {{-- Native <details> keeps this accessible and JS-free. --}}
                <details class="group px-5" @if ($loop->first) open @endif>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 py-4 text-sm font-semibold text-ink-900 marker:hidden">
                        {{ $faq->question }}
                        <x-ui-icon name="chevron-down" class="size-4 shrink-0 text-ink-400 transition-transform duration-200 group-open:rotate-180" />
                    </summary>
                    <div class="prose-article pb-5 text-sm">{!! $faq->answer !!}</div>
                </details>
            @endforeach
        </div>
    </div>
@endif
