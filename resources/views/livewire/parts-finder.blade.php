<div class="card p-5 sm:p-7">
    <label for="parts-machine" class="field-label">{{ __('parts.search_label') }}</label>

    @if ($selected)
        {{-- The machine is chosen: show it as a token that can be taken back off. --}}
        <div class="mt-2 flex flex-wrap items-center gap-3 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3">
            <x-ui-icon name="check-circle" class="size-5 shrink-0 text-brand-700" />
            <span class="text-sm font-bold text-ink-900">{{ $selected->name }}</span>
            @if ($selected->model_code)
                <span class="tabular text-xs text-ink-500">{{ $selected->model_code }}</span>
            @endif
            <button type="button" wire:click="clear" class="btn-ghost btn-sm ms-auto">{{ __('ui.clear') }}</button>
        </div>
    @else
        <input id="parts-machine"
               type="search"
               class="field mt-2"
               wire:model.live.debounce.300ms="search"
               placeholder="{{ __('parts.search_placeholder') }}"
               autocomplete="off">

        @if (trim($search) !== '')
            <ul class="mt-3 divide-y divide-ink-100 overflow-hidden rounded-xl border border-ink-100">
                @forelse ($matches as $machine)
                    <li>
                        <button type="button"
                                wire:click="select('{{ $machine->slug }}')"
                                class="flex min-h-12 w-full items-center gap-3 px-4 text-start text-sm hover:bg-ink-50">
                            <span class="flex-1 font-semibold text-ink-900">{{ $machine->name }}</span>
                            @if ($machine->model_code)
                                <span class="tabular text-xs text-ink-400">{{ $machine->model_code }}</span>
                            @endif
                        </button>
                    </li>
                @empty
                    <li class="px-4 py-4 text-sm text-ink-500">{{ __('parts.no_machine') }}</li>
                @endforelse
            </ul>
        @endif
    @endif

    @if ($selected)
        <div class="mt-7">
            @if ($parts->isNotEmpty())
                <p class="eyebrow mb-4">{{ __('parts.fits', ['name' => $selected->name]) }}</p>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($parts as $part)
                        <x-product-card :product="$part" :show-compare="false" wire:key="part-{{ $part->id }}" />
                    @endforeach
                </div>
            @else
                <x-empty-state :title="__('parts.none_listed')"
                               :href="route('contact')"
                               :link-label="__('ui.free_consultation')"
                               icon="wrench" />
            @endif
        </div>
    @endif
</div>
