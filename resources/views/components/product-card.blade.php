@props(['product', 'showCompare' => true])

<article class="card-hover tick-frame group flex h-full flex-col overflow-hidden">
    <a href="{{ $product->url() }}"
       class="relative block aspect-4/3 overflow-hidden bg-linear-to-b from-ink-50 to-white">
        {{--
            A soft radial floor under the machine. Product shots arrive on
            transparent or white backgrounds, and a flat grey panel leaves them
            looking cut out; this seats them without needing a shadow baked
            into every image.
        --}}
        <span class="pointer-events-none absolute inset-x-6 bottom-5 h-10 rounded-[50%] bg-ink-900/8 blur-xl"></span>

        <img src="{{ $product->coverUrl() }}"
             alt="{{ $product->name }}"
             loading="lazy"
             decoding="async"
             class="relative size-full object-contain p-6 transition-transform duration-500 ease-[var(--ease-out-quart)] group-hover:scale-[1.06]">

        @if ($product->is_rentable)
            <span class="absolute top-3.5 start-3.5 rounded-md bg-accent-400 px-2 py-1 text-[11px] leading-none font-bold text-ink-950 shadow-[var(--shadow-e1)]">
                {{ __('nav.rental') }}
            </span>
        @endif

        @if ($product->stock_status?->value !== 'in_stock')
            <span class="absolute top-3.5 end-3.5 rounded-md border border-ink-200 bg-white/90 px-2 py-1 text-[11px] leading-none font-semibold text-ink-600 backdrop-blur-sm">
                {{ $product->stock_status?->getLabel() }}
            </span>
        @endif
    </a>

    <div class="flex flex-1 flex-col border-t border-ink-100 p-5">
        @if ($product->brand)
            <p class="text-[11px] font-semibold tracking-[0.1em] text-ink-400 uppercase">{{ $product->brand->name }}</p>
        @endif

        <h3 class="mt-1.5 text-base leading-7 font-bold text-ink-900">
            <a href="{{ $product->url() }}" class="transition-colors hover:text-brand-700">{{ $product->name }}</a>
        </h3>

        {{--
            Three numbers a buyer scans before anything else. Ruled columns
            rather than free-floating centred pairs: the hairlines are what let
            the eye compare the same figure across a row of cards.
        --}}
        @php $specs = $product->keySpecs()->take(3); @endphp
        @if ($specs->isNotEmpty())
            <dl class="mt-4 grid grid-cols-3 rounded-lg border border-ink-100 bg-ink-50/60 py-3 text-center [&>*+*]:border-s [&>*+*]:border-ink-200/70">
                @foreach ($specs as $spec)
                    <div class="px-2">
                        <dd class="spec-value">{{ $spec['value'] }}</dd>
                        <dt class="spec-label">{{ $spec['label'] }}</dt>
                    </div>
                @endforeach
            </dl>
        @endif

        {{--
            Which sites this machine is for. A procurement manager scanning a
            row of cards is matching them against one building, and this is the
            line that lets them discard three of four without opening any of
            them. Only rendered when the relation is already loaded, so a card
            can never cost a query per row.
        --}}
        @if ($product->relationLoaded('environments') && $product->environments->isNotEmpty())
            <div class="mt-4">
                <p class="text-[11px] text-ink-400">{{ __('ui.product.suitable_for') }}</p>
                <p class="mt-1.5 flex flex-wrap items-center gap-x-1.5 gap-y-1 text-xs leading-5 text-ink-600">
                    @foreach ($product->environments->take(3) as $environment)
                        <span>{{ $environment->name }}</span>
                        @if (! $loop->last)
                            <span class="text-ink-300">•</span>
                        @endif
                    @endforeach
                </p>
            </div>
        @endif

        <div class="mt-auto pt-5">
            @if ($product->showsPrice())
                <p class="tabular mb-3 text-lg leading-none font-black text-ink-900">
                    {{ number_format((float) $product->price) }}
                    <span class="text-xs font-medium text-ink-500">{{ __('ui.currency') }}</span>
                </p>
            @else
                <p class="mb-3 flex items-center gap-1.5 text-sm font-semibold text-ink-500">
                    <span class="size-1.5 rounded-full bg-accent-500"></span>
                    {{ __('ui.price_on_request') }}
                </p>
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
