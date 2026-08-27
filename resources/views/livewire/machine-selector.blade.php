<div class="space-y-8">

    {{-- ---------------------------------------------------------------- --}}
    {{-- Inputs                                                           --}}
    {{-- ---------------------------------------------------------------- --}}
    <form wire:submit="calculate" class="card grid gap-4 p-6 sm:grid-cols-2 sm:p-8 lg:grid-cols-4">
        <div class="lg:col-span-2">
            <label for="sel-area" class="field-label">{{ __('ui.selector.area') }}</label>
            <input id="sel-area" type="number" min="10" step="10" class="field tabular"
                   wire:model="area" placeholder="{{ __('ui.selector.area_placeholder') }}" required>
            @error('area') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="sel-shift" class="field-label">{{ __('ui.selector.shift') }}</label>
            <input id="sel-shift" type="number" min="1" max="24" class="field tabular" wire:model="shiftHours">
            @error('shiftHours') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="sel-env" class="field-label">{{ __('ui.selector.environment') }}</label>
            <select id="sel-env" class="field" wire:model="environment">
                <option value="">{{ __('ui.selector.environment_any') }}</option>
                @foreach ($environments as $env)
                    <option value="{{ $env->slug }}">{{ $env->name }}</option>
                @endforeach
            </select>
        </div>

        <label class="check-row sm:col-span-2">
            <input type="checkbox" wire:model="rentalOnly" class="check-box">
            {{ __('ui.selector.rental_only') }}
        </label>

        <div class="sm:col-span-2 lg:col-span-2 lg:justify-self-end">
            <button type="submit" class="btn-primary w-full lg:w-auto" wire:loading.attr="disabled">
                <x-ui-icon name="calculator" class="size-4" />
                {{ __('ui.calculate') }}
            </button>
        </div>
    </form>

    {{-- ---------------------------------------------------------------- --}}
    {{-- Result                                                           --}}
    {{-- ---------------------------------------------------------------- --}}
    @if ($result)
        <div class="rounded-2xl bg-ink-900 p-6 text-white sm:p-8">
            <h2 class="text-lg font-bold">
                {{ __('ui.selector.result_title', ['area' => number_format($result['area'])]) }}
            </h2>

            <dl class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl bg-white/5 p-4">
                    <dt class="text-xs text-ink-400">{{ __('ui.selector.recommended_class') }}</dt>
                    <dd class="mt-1 text-base font-bold text-accent-400">
                        {{ __('enums.machine_class.'.$result['class']['key']) }}
                    </dd>
                </div>

                <div class="rounded-xl bg-white/5 p-4">
                    <dt class="text-xs text-ink-400">{{ __('ui.selector.required_productivity') }}</dt>
                    <dd class="tabular mt-1 text-base font-bold">
                        {{ number_format($result['required_productivity']) }} m²/h
                    </dd>
                </div>

                <div class="rounded-xl bg-white/5 p-4">
                    <dt class="text-xs text-ink-400">{{ __('ui.selector.estimated_time') }}</dt>
                    <dd class="tabular mt-1 text-base font-bold">
                        {{ $result['estimated_hours'] ? $result['estimated_hours'].' '.__('ui.selector.hours') : '—' }}
                    </dd>
                </div>
            </dl>

            <p class="mt-5 text-xs leading-6 text-ink-400">
                {{ __('ui.selector.efficiency_note', ['efficiency' => round($result['efficiency'] * 100)]) }}
            </p>
        </div>

        @if ($result['recommendations']->isNotEmpty())
            <div>
                <h3 class="mb-5 text-lg font-bold text-ink-900">{{ __('ui.selector.matches') }}</h3>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($result['recommendations'] as $product)
                        <x-product-card :product="$product" wire:key="rec-{{ $product->id }}" />
                    @endforeach
                </div>
            </div>
        @endif

        @if ($result['alternatives']->isNotEmpty())
            <div>
                <h3 class="mb-5 text-lg font-bold text-ink-900">{{ __('ui.selector.alternatives') }}</h3>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($result['alternatives'] as $product)
                        <x-product-card :product="$product" wire:key="alt-{{ $product->id }}" />
                    @endforeach
                </div>
            </div>
        @endif

        @if ($result['recommendations']->isEmpty() && $result['alternatives']->isEmpty())
            <x-empty-state :title="__('ui.selector.no_match')"
                           :href="route('contact')"
                           :link-label="__('ui.free_consultation')"
                           icon="wrench" />
        @endif

        {{-- ------------------------------------------------------------ --}}
        {{-- Consultation capture                                         --}}
        {{-- ------------------------------------------------------------ --}}
        <div class="card p-6 sm:p-8">
            @if ($submitted)
                <p class="flex items-center justify-center gap-2 py-4 text-sm font-semibold text-emerald-700">
                    <x-ui-icon name="check-circle" class="size-5" />
                    {{ __('ui.selector.thanks') }}
                </p>
            @elseif ($showLeadForm)
                <form wire:submit="requestConsultation" class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="sel-name" class="field-label">{{ __('ui.form.name') }}</label>
                        <input id="sel-name" type="text" class="field" wire:model="name" required>
                        @error('name') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="sel-phone" class="field-label">{{ __('ui.form.phone') }}</label>
                        <input id="sel-phone" type="tel" dir="ltr" class="field tabular text-start" wire:model="phone" required>
                        @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="sel-city" class="field-label">{{ __('ui.form.city') }}</label>
                        <input id="sel-city" type="text" class="field" wire:model="city">
                    </div>

                    <div>
                        <label for="sel-company" class="field-label">{{ __('ui.form.company') }}</label>
                        <input id="sel-company" type="text" class="field" wire:model="company">
                    </div>

                    <div class="sm:col-span-2">
                        <button type="submit" class="btn-primary w-full sm:w-auto" wire:loading.attr="disabled">
                            {{ __('ui.submit') }}
                        </button>
                    </div>
                </form>
            @else
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-ink-900">{{ __('ui.selector.want_advice') }}</h3>
                        <p class="mt-1 text-sm text-ink-500">{{ __('ui.selector.advice_hint') }}</p>
                    </div>

                    <button type="button" class="btn-dark" wire:click="$set('showLeadForm', true)">
                        {{ __('ui.free_consultation') }}
                    </button>
                </div>
            @endif
        </div>
    @else
        {{--
            Before a calculation there is nothing to show, and an empty page
            under a form reads as a page that is still loading. These three
            lines say what the calculator is about to do with the number, which
            is also what makes a visitor trust the answer when it appears.
        --}}
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach (['area', 'efficiency', 'shortlist'] as $index => $step)
                <div class="card flex h-full flex-col p-6">
                    <span class="tabular grid size-9 place-items-center rounded-lg bg-brand-50 text-sm font-black text-brand-700">
                        {{ $index + 1 }}
                    </span>
                    <h3 class="mt-4 text-sm font-bold text-ink-900">{{ __('ui.selector.how.'.$step.'_title') }}</h3>
                    <p class="mt-2 text-sm leading-6 text-ink-500">{{ __('ui.selector.how.'.$step.'_body') }}</p>
                </div>
            @endforeach
        </div>
    @endif
</div>
