<div>
    @if ($products->isEmpty())
        <x-empty-state :title="__('ui.compare_page.empty')"
                       :body="__('ui.compare_page.empty_hint')"
                       :href="route('products.index')"
                       :link-label="__('ui.compare_page.browse')"
                       icon="scale" />
    @else
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <label class="check-row font-medium">
                <input type="checkbox" wire:model.live="differencesOnly"
                       class="check-box">
                {{ __('ui.compare_page.differences_only') }}
                <span class="tabular text-xs text-ink-400">
                    ({{ __('ui.compare_page.showing', ['shown' => count($rows), 'total' => $totalRows]) }})
                </span>
            </label>

            <button type="button" wire:click="clear" class="btn-ghost btn-sm">
                <x-ui-icon name="trash" class="size-4" />
                {{ __('ui.clear') }}
            </button>
        </div>

        {{--
            The header row sticks so the machine names stay visible while the
            reader scrolls a long specification table.
        --}}
        <div class="overflow-x-auto rounded-[var(--radius-card)] border border-ink-100">
            <table class="w-full min-w-[48rem] border-collapse text-sm">
                <thead>
                    <tr class="sticky top-16 z-10 bg-white shadow-[0_1px_0_var(--color-ink-100)]">
                        <th scope="col" class="w-44 p-4 text-start align-bottom text-xs font-medium text-ink-400">
                            {{ __('ui.product.specs') }}
                        </th>

                        @foreach ($products as $product)
                            <th scope="col" class="w-64 p-4 align-top" wire:key="head-{{ $product->id }}">
                                <div class="relative">
                                    <button type="button"
                                            wire:click="remove({{ $product->id }})"
                                            class="absolute top-0 end-0 grid size-6 place-items-center rounded-full text-ink-300 hover:bg-ink-50 hover:text-ink-900"
                                            aria-label="{{ __('ui.clear') }}">
                                        <x-ui-icon name="close" class="size-3.5" />
                                    </button>

                                    <a href="{{ $product->url() }}" class="block">
                                        <img src="{{ $product->coverUrl() }}" alt="{{ $product->name }}"
                                             class="mx-auto h-28 w-auto object-contain">
                                        <span class="mt-3 block text-sm leading-6 font-bold text-ink-900 hover:text-brand-700">
                                            {{ $product->name }}
                                        </span>
                                    </a>

                                    @if ($product->brand)
                                        <span class="mt-1 block text-xs font-normal text-ink-400">{{ $product->brand->name }}</span>
                                    @endif

                                    <a href="{{ $product->url() }}#quote" class="btn-primary btn-sm mt-3 w-full">
                                        {{ __('ui.request_price') }}
                                    </a>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="divide-y divide-ink-100">
                    @foreach ($rows as $row)
                        <tr class="odd:bg-ink-50/60" wire:key="row-{{ $row['key'] }}">
                            <th scope="row" class="p-4 text-start font-medium text-ink-500">{{ $row['label'] }}</th>

                            @foreach ($row['values'] as $index => $value)
                                <td @class([
                                        'tabular p-4 font-semibold',
                                        'text-ink-900' => $row['best'] !== $index,
                                        'text-emerald-700' => $row['best'] === $index,
                                        'text-ink-300' => $value === null,
                                    ])>
                                    {{ $value ?? '—' }}

                                    @if ($row['best'] === $index)
                                        <span class="ms-1 align-middle text-[10px] font-bold text-emerald-600">
                                            ({{ __('ui.compare_page.best') }})
                                        </span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
