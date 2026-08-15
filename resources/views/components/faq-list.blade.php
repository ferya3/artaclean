@props(['faqs', 'title' => null])

@if ($faqs->isNotEmpty())
    <div {{ $attributes->merge(['class' => '']) }}>
        @if ($title)
            <h2 class="mb-6 text-xl font-bold text-ink-900">{{ $title }}</h2>
        @endif

        <div class="divide-y divide-ink-100 overflow-hidden rounded-[var(--radius-card)] border border-ink-100 bg-white shadow-[var(--shadow-e1)]">
            @foreach ($faqs as $faq)
                {{-- Native <details> keeps this accessible and JS-free. --}}
                <details class="group px-5 transition-colors duration-200 open:bg-ink-50/50 hover:bg-ink-50/50" @if ($loop->first) open @endif>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 py-4.5 text-sm leading-6 font-semibold text-ink-900 marker:hidden">
                        {{ $faq->question }}
                        <span class="grid size-7 shrink-0 place-items-center rounded-full border border-ink-200 bg-white text-ink-500 transition-[transform,color,border-color] duration-300 ease-[var(--ease-out-quart)] group-open:rotate-180 group-open:border-brand-200 group-open:text-brand-600">
                            <x-ui-icon name="chevron-down" class="size-3.5" />
                        </span>
                    </summary>
                    <div class="prose-article pb-5 text-sm">{!! $faq->answer !!}</div>
                </details>
            @endforeach
        </div>
    </div>
@endif
