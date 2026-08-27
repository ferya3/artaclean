<div class="card p-6 sm:p-8">
    @if ($submitted)
        <div class="py-6 text-center">
            <span class="mx-auto grid size-14 place-items-center rounded-full bg-emerald-50 text-emerald-600">
                <x-ui-icon name="check-circle" class="size-7" />
            </span>

            <h2 class="mt-5 text-xl font-bold text-ink-900">{{ __('ui.form.success_title') }}</h2>
            <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-ink-500">{{ __('ui.form.success_body') }}</p>

            @if ($unlockedDownloadUrl)
                <p class="mt-6 text-sm text-ink-600">{{ __('contact.download_ready') }}</p>
                <a href="{{ $unlockedDownloadUrl }}" class="btn-primary mt-3">
                    <x-ui-icon name="download" class="size-4" />
                    {{ __('ui.download_catalog') }}
                </a>
            @endif
        </div>
    @else
        <form wire:submit="submit" class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="c-name" class="field-label">{{ __('ui.form.name') }}</label>
                <input id="c-name" type="text" class="field" wire:model="name" required autocomplete="name">
                @error('name') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="c-phone" class="field-label">{{ __('ui.form.phone') }}</label>
                <input id="c-phone" type="tel" dir="ltr" class="field tabular text-start" wire:model="phone" required
                       inputmode="numeric" autocomplete="tel" placeholder="09121234567">
                @error('phone') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="c-email" class="field-label">
                    {{ __('ui.form.email') }} <span class="text-ink-400">({{ __('ui.optional') }})</span>
                </label>
                <input id="c-email" type="email" dir="ltr" class="field text-start" wire:model="email" autocomplete="email">
                @error('email') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="c-city" class="field-label">{{ __('ui.form.city') }}</label>
                <input id="c-city" type="text" class="field" wire:model="city">
            </div>

            <div>
                <label for="c-company" class="field-label">{{ __('ui.form.company') }}</label>
                <input id="c-company" type="text" class="field" wire:model="company" autocomplete="organization">
            </div>

            <div>
                <label for="c-business" class="field-label">{{ __('ui.form.business_type') }}</label>
                <select id="c-business" class="field" wire:model="businessType">
                    <option value="">{{ __('ui.form.select') }}</option>
                    @foreach ($businessTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label for="c-message" class="field-label">{{ __('ui.form.message') }}</label>
                <textarea id="c-message" rows="4" class="field" wire:model="message" required
                          placeholder="{{ __('ui.form.note_placeholder') }}"></textarea>
                @error('message') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            {{--
                This is the page every price enquiry on the site ends at, so
                the button is sized for it: full width on a phone, and the same
                accent the hero used to ask for the enquiry in the first place,
                rather than a small tertiary-looking button in a corner.
            --}}
            <div class="flex flex-col-reverse gap-3 border-t border-ink-100 pt-5 sm:col-span-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs leading-5 text-ink-400">{{ __('ui.form.privacy') }}</p>

                <button type="submit" class="btn-accent btn-lg w-full sm:w-auto" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">{{ __('ui.submit') }}</span>
                    <span wire:loading wire:target="submit">{{ __('ui.loading') }}</span>
                </button>
            </div>
        </form>
    @endif
</div>
