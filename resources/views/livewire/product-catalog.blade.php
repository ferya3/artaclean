<div class="grid gap-8 lg:grid-cols-[17rem_1fr] lg:gap-10">

    {{-- ---------------------------------------------------------------- --}}
    {{-- Filter panel                                                     --}}
    {{-- ---------------------------------------------------------------- --}}
    <aside x-data="{ open: false }" class="lg:sticky lg:top-28 lg:self-start">
        <button type="button"
                class="btn-outline w-full lg:hidden"
                @click="open = !open"
                :aria-expanded="open">
            <x-ui-icon name="menu" class="size-4" />
            {{ __('ui.filters') }}
            @if ($filters->hasActiveFilters())
                <span class="ms-auto rounded-full bg-brand-600 px-2 py-0.5 text-[11px] text-white">•</span>
            @endif
        </button>

        <div class="mt-3 space-y-6 lg:mt-0 lg:block" :class="open ? 'block' : 'hidden lg:block'">

            @if ($filters->hasActiveFilters())
                <button type="button" wire:click="clearFilters" class="btn-ghost btn-sm w-full justify-start !px-0 text-brand-700">
                    <x-ui-icon name="close" class="size-3.5" />
                    {{ __('ui.clear_filters') }}
                </button>
            @endif

            {{-- Search --}}
            <div>
                <label for="catalog-search" class="field-label">{{ __('ui.search_placeholder') }}</label>
                <input id="catalog-search"
                       type="search"
                       class="field"
                       wire:model.live.debounce.400ms="search"
                       placeholder="{{ __('ui.search_placeholder') }}">
            </div>

            {{-- Coverage area: the filter that maps to how buyers think. --}}
            <div>
                <label for="coverage" class="field-label">{{ __('ui.selector.area') }}</label>
                <input id="coverage"
                       type="number"
                       min="10"
                       step="50"
                       class="field tabular"
                       wire:model.live.debounce.600ms="coverageArea"
                       placeholder="{{ __('ui.selector.area_placeholder') }}">
            </div>

            {{-- Brands --}}
            @if (! empty($facets['brands']))
                <fieldset>
                    <legend class="field-label">{{ __('admin.field.brand') }}</legend>
                    <div class="max-h-56 space-y-1.5 overflow-y-auto pe-1">
                        @foreach ($facets['brands'] as $slug => $facet)
                            <label class="check-row">
                                <input type="checkbox" value="{{ $slug }}" wire:model.live="brands"
                                       class="check-box">
                                <span class="flex-1 truncate">{{ $facet['name'] }}</span>
                                <span class="tabular text-xs text-ink-400">{{ $facet['count'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endif

            {{-- Power source --}}
            <fieldset>
                <legend class="field-label">{{ __('specs.power_source') }}</legend>
                <div class="space-y-1.5">
                    @foreach ($powerSourceOptions as $value => $label)
                        @continue(! isset($facets['power_sources'][$value]) && ! in_array($value, $powerSources, true))
                        <label class="check-row">
                            <input type="checkbox" value="{{ $value }}" wire:model.live="powerSources"
                                   class="check-box">
                            <span class="flex-1">{{ $label }}</span>
                            <span class="tabular text-xs text-ink-400">{{ $facets['power_sources'][$value] ?? 0 }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            {{-- Operator type --}}
            @if (! empty($facets['operator_types']))
                <fieldset>
                    <legend class="field-label">{{ __('specs.operator_type') }}</legend>
                    <div class="space-y-1.5">
                        @foreach ($operatorTypeOptions as $value => $label)
                            @continue(! isset($facets['operator_types'][$value]) && ! in_array($value, $operatorTypes, true))
                            <label class="check-row">
                                <input type="checkbox" value="{{ $value }}" wire:model.live="operatorTypes"
                                       class="check-box">
                                <span class="flex-1">{{ $label }}</span>
                                <span class="tabular text-xs text-ink-400">{{ $facets['operator_types'][$value] }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endif

            {{-- Numeric ranges --}}
            <fieldset>
                <legend class="field-label">{{ __('specs.tank_capacity_l') }} (L)</legend>
                <div class="flex items-center gap-2">
                    <input type="number" class="field tabular" wire:model.live.debounce.600ms="tankCapacityMin"
                           placeholder="{{ $facets['ranges']['tank']['min'] ?? 0 }}" aria-label="min">
                    <span class="text-ink-300">—</span>
                    <input type="number" class="field tabular" wire:model.live.debounce.600ms="tankCapacityMax"
                           placeholder="{{ $facets['ranges']['tank']['max'] ?? 0 }}" aria-label="max">
                </div>
            </fieldset>

            <fieldset>
                <legend class="field-label">{{ __('specs.power_watt') }} (W)</legend>
                <div class="flex items-center gap-2">
                    <input type="number" class="field tabular" wire:model.live.debounce.600ms="powerMin"
                           placeholder="{{ $facets['ranges']['power']['min'] ?? 0 }}" aria-label="min">
                    <span class="text-ink-300">—</span>
                    <input type="number" class="field tabular" wire:model.live.debounce.600ms="powerMax"
                           placeholder="{{ $facets['ranges']['power']['max'] ?? 0 }}" aria-label="max">
                </div>
            </fieldset>

            <fieldset>
                <legend class="field-label">{{ __('specs.cleaning_width_mm') }} (mm)</legend>
                <div class="flex items-center gap-2">
                    <input type="number" class="field tabular" wire:model.live.debounce.600ms="cleaningWidthMin"
                           placeholder="{{ $facets['ranges']['width']['min'] ?? 0 }}" aria-label="min">
                    <span class="text-ink-300">—</span>
                    <input type="number" class="field tabular" wire:model.live.debounce.600ms="cleaningWidthMax"
                           placeholder="{{ $facets['ranges']['width']['max'] ?? 0 }}" aria-label="max">
                </div>
            </fieldset>

            {{-- Category-scoped attributes --}}
            @foreach ($attributes as $attribute)
                @continue($attribute->values->isEmpty())
                <fieldset>
                    <legend class="field-label">{{ $attribute->name }}</legend>
                    <div class="space-y-1.5">
                        @foreach ($attribute->values as $value)
                            <label class="check-row">
                                <input type="checkbox" value="{{ $value->id }}" wire:model.live="attributeValueIds"
                                       class="check-box">
                                <span class="flex-1">{{ $value->value }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach

            {{-- Toggles --}}
            <div class="space-y-2 border-t border-ink-100 pt-5">
                <label class="check-row">
                    <input type="checkbox" wire:model.live="rentableOnly"
                           class="check-box">
                    {{ __('ui.selector.rental_only') }}
                </label>
                <label class="check-row">
                    <input type="checkbox" wire:model.live="inStockOnly"
                           class="check-box">
                    {{ __('enums.stock_status.in_stock') }}
                </label>
            </div>
        </div>
    </aside>

    {{-- ---------------------------------------------------------------- --}}
    {{-- Results                                                          --}}
    {{-- ---------------------------------------------------------------- --}}
    <div>
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <p class="tabular text-sm text-ink-500" wire:loading.class="opacity-40">
                {{ __('ui.results_count', ['count' => $products->total()]) }}
            </p>

            <div class="flex items-center gap-2">
                <label for="sort" class="text-sm text-ink-500">{{ __('ui.sort_by') }}</label>
                <select id="sort" wire:model.live="sort" class="field !w-auto !py-2 text-sm">
                    @foreach (['default', 'newest', 'popular', 'productivity', 'tank', 'power'] as $option)
                        <option value="{{ $option }}">{{ __('ui.sort.'.$option) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Active filter chips --}}
        @if ($brands !== [])
            <div class="mb-5 flex flex-wrap gap-2">
                @foreach ($brands as $slug)
                    <button type="button" wire:click="removeBrand('{{ $slug }}')" class="chip hover:border-ink-900">
                        {{ $facets['brands'][$slug]['name'] ?? $slug }}
                        <x-ui-icon name="close" class="size-3" />
                    </button>
                @endforeach
            </div>
        @endif

        <div wire:loading.class="pointer-events-none opacity-50" class="transition-opacity duration-200">
            @if ($products->isEmpty())
                <x-empty-state :title="__('ui.no_results')"
                               :body="__('ui.no_results_hint')"
                               :href="route('contact')"
                               :link-label="__('ui.free_consultation')" />
            @else
                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" wire:key="p-{{ $product->id }}" />
                    @endforeach
                </div>

                <div class="mt-10">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
</div>
