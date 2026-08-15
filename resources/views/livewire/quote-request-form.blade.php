<div class="card p-6 sm:p-8">
    @if ($submitted)
        {{-- Success state stays on the page: no redirect, no lost context. --}}
        <div class="py-6 text-center">
            <span class="mx-auto grid size-14 place-items-center rounded-full bg-emerald-50 text-emerald-600">
                <x-ui-icon name="check-circle" class="size-7" />
            </span>

            <h2 class="mt-5 text-xl font-bold text-ink-900">{{ __('ui.form.success_title') }}</h2>
            <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-ink-500">{{ __('ui.form.success_body') }}</p>

            <p class="tabular mt-5 inline-flex items-center gap-2 rounded-lg bg-ink-50 px-4 py-2.5 text-sm">
                <span class="text-ink-500">{{ __('ui.form.reference') }}:</span>
                <b class="text-ink-900">{{ $reference }}</b>
            </p>
        </div>
    @else
        <div class="mb-6">
            <p class="eyebrow mb-2">{{ $mode === 'rental' ? __('nav.rental') : __('ui.request_price') }}</p>
            <h2 class="text-xl font-bold text-ink-900">
                {{ $mode === 'rental' ? __('ui.request_rental') : __('ui.request_price') }}
            </h2>

            @if ($product)
                <p class="mt-2 text-sm text-ink-500">{{ $product->name }}</p>
            @endif

            @if ($mode !== 'rental')
                <p class="mt-2 text-sm leading-6 text-ink-500">{{ __('ui.price_note') }}</p>
            @endif
        </div>

        <form wire:submit="submit" class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="q-name-{{ $mode }}" class="field-label">{{ __('ui.form.name') }}</label>
                <input id="q-name-{{ $mode }}" type="text" class="field" wire:model="name" required autocomplete="name">
                @error('name') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="q-phone-{{ $mode }}" class="field-label">{{ __('ui.form.phone') }}</label>
                <input id="q-phone-{{ $mode }}" type="tel" dir="ltr" class="field tabular text-start" wire:model="phone" required
                       inputmode="numeric" autocomplete="tel" placeholder="09121234567">
                @error('phone') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="q-city-{{ $mode }}" class="field-label">
                    {{ __('ui.form.city') }} <span class="text-ink-400">({{ __('ui.optional') }})</span>
                </label>
                <input id="q-city-{{ $mode }}" type="text" class="field" wire:model="city" autocomplete="address-level2">
                @error('city') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="q-company-{{ $mode }}" class="field-label">
                    {{ __('ui.form.company') }} <span class="text-ink-400">({{ __('ui.optional') }})</span>
                </label>
                <input id="q-company-{{ $mode }}" type="text" class="field" wire:model="company" autocomplete="organization">
                @error('company') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="q-business-{{ $mode }}" class="field-label">{{ __('ui.form.business_type') }}</label>
                <select id="q-business-{{ $mode }}" class="field" wire:model="businessType">
                    <option value="">{{ __('ui.form.select') }}</option>
                    @foreach ($businessTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="q-qty-{{ $mode }}" class="field-label">{{ __('ui.form.quantity') }}</label>
                <input id="q-qty-{{ $mode }}" type="number" min="1" class="field tabular" wire:model="quantity">
                @error('quantity') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            @if ($mode === 'rental')
                <div>
                    <label for="q-period" class="field-label">{{ __('ui.form.rental_period') }}</label>
                    <select id="q-period" class="field" wire:model="rentalPeriod">
                        @foreach ($rentalPeriods as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('rentalPeriod') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="q-duration" class="field-label">{{ __('ui.form.rental_duration') }}</label>
                    <input id="q-duration" type="number" min="1" class="field tabular" wire:model="rentalDuration">
                    @error('rentalDuration') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="q-start" class="field-label">
                        {{ __('ui.form.rental_start') }} <span class="text-ink-400">({{ __('ui.optional') }})</span>
                    </label>
                    <input id="q-start" type="date" class="field" wire:model="rentalStartsAt">
                </div>
            @endif

            <div class="sm:col-span-2">
                <label for="q-note-{{ $mode }}" class="field-label">
                    {{ __('ui.form.message') }} <span class="text-ink-400">({{ __('ui.optional') }})</span>
                </label>
                <textarea id="q-note-{{ $mode }}" rows="3" class="field" wire:model="note"
                          placeholder="{{ __('ui.form.note_placeholder') }}"></textarea>
                @error('note') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 sm:col-span-2">
                <p class="text-xs text-ink-400">{{ __('ui.form.privacy') }}</p>

                <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">{{ __('ui.submit') }}</span>
                    <span wire:loading wire:target="submit">{{ __('ui.loading') }}</span>
                </button>
            </div>
        </form>
    @endif
</div>
