@props(['product', 'showCompare' => true])

<article class="card-hover group flex h-full flex-col overflow-hidden">
    <a href="{{ $product->url() }}" class="relative block aspect-4/3 overflow-hidden bg-ink-50">
        <img src="{{ $product->coverUrl() }}"
             alt="{{ $product->name }}"
             loading="lazy"
             decoding="async"
             class="size-full object-contain p-5 transition-transform duration-500 ease-[var(--ease-out-quart)] group-hover:scale-[1.04]">

        @if ($product->is_rentable)
            <span class="absolute top-3 start-3 rounded-full bg-accent-500 px-2.5 py-1 text-[11px] font-bold text-ink-900">
                {{ __('nav.rental') }}
            </span>
        @endif

        @if ($product->stock_status?->value !== 'in_stock')
            <span class="absolute top-3 end-3 rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-semibold text-ink-600">
                {{ $product->stock_status?->getLabel() }}
            </span>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-5">
        @if ($product->brand)
            <p class="text-xs font-medium text-ink-400">{{ $product->brand->name }}</p>
        @endif

        <h3 class="mt-1 text-base leading-7 font-bold text-ink-900">
            <a href="{{ $product->url() }}" class="hover:text-brand-700">{{ $product->name }}</a>
        </h3>

        {{-- Three numbers a buyer scans before anything else. --}}
        @php $specs = $product->keySpecs()->take(3); @endphp
        @if ($specs->isNotEmpty())
            <dl class="mt-4 grid grid-cols-3 gap-2 border-t border-ink-100 pt-4 text-center">
                @foreach ($specs as $spec)
                    <div>
                        <dt class="text-[11px] leading-4 text-ink-400">{{ $spec['label'] }}</dt>
                        <dd class="tabular mt-0.5 text-sm font-bold text-ink-900">{{ $spec['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif

        <div class="mt-auto pt-5">
            @if ($product->showsPrice())
                <p class="tabular mb-3 text-lg font-black text-ink-900">
                    {{ number_format((float) $product->price) }}
                    <span class="text-xs font-medium text-ink-500">{{ __('ui.price_on_request') === '' ? '' : 'ریال' }}</span>
                </p>
            @else
                <p class="mb-3 text-sm font-semibold text-ink-500">{{ __('ui.price_on_request') }}</p>
            @endif

            <div class="flex items-center gap-2">
                <a href="{{ $product->url() }}" class="btn-primary btn-sm flex-1">{{ __('ui.request_price') }}</a>

                @if ($showCompare)
                    <livewire:compare-button :product-id="$product->id" :compact="true" :key="'cmp-'.$product->id" />
                @endif
            </div>
        </div>
    </div>
</article>
