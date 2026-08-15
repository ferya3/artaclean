<div>
    @if ($products->isNotEmpty())
        {{-- Sits above the WhatsApp button's row on mobile by reserving the start side. --}}
        <div class="fixed inset-x-0 bottom-0 z-40 border-t border-ink-200 bg-white/95 backdrop-blur">
            <div class="container-page flex items-center gap-4 py-3">
                <div class="no-scrollbar flex flex-1 items-center gap-3 overflow-x-auto">
                    <span class="hidden shrink-0 text-xs font-semibold text-ink-500 sm:block">
                        {{ __('compare.bar_title') }}
                        <span class="tabular">({{ $products->count() }}/{{ $max }})</span>
                    </span>

                    @foreach ($products as $product)
                        <div class="relative shrink-0" wire:key="bar-{{ $product->id }}">
                            <img src="{{ $product->coverUrl() }}" alt="{{ $product->name }}"
                                 class="size-12 rounded-lg border border-ink-200 bg-white object-contain p-1">

                            <button type="button"
                                    wire:click="remove({{ $product->id }})"
                                    class="absolute -top-1.5 -end-1.5 grid size-5 place-items-center rounded-full bg-ink-900 text-white"
                                    aria-label="{{ __('ui.clear') }}">
                                <x-ui-icon name="close" class="size-3" />
                            </button>
                        </div>
                    @endforeach
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <button type="button" wire:click="clear" class="btn-ghost btn-sm hidden sm:inline-flex">
                        {{ __('ui.clear') }}
                    </button>
                    <a href="{{ route('compare') }}" class="btn-primary btn-sm">
                        {{ __('compare.go_compare') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Keeps the WhatsApp bubble and page bottom clear of the fixed bar. --}}
        <div class="h-20"></div>
    @endif
</div>
