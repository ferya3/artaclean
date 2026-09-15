@php
    /**
     * The four questions, in the order a buyer can actually answer them:
     * what they are cleaning, what is on it, how much of it, and how often.
     * Area comes third on purpose — it is the one people have to think about.
     */
    $steps = [
        1 => ['field' => 'surface', 'value' => $surface, 'options' => $surfaces],
        2 => ['field' => 'soil', 'value' => $soil, 'options' => $soils],
        3 => ['field' => 'areaBand', 'value' => $areaBand, 'options' => $areaBands],
        4 => ['field' => 'frequency', 'value' => $frequency, 'options' => $frequencies],
    ];

    $answered = collect($steps)->filter(fn ($s) => filled($s['value']))->count();
@endphp

<div class="card overflow-hidden">
    {{-- Progress: four dots and a rule, so the end is visible from the start. --}}
    <div class="flex items-center gap-3 border-b border-ink-100 bg-ink-50/70 px-5 py-4 sm:px-7">
        <p class="text-sm font-bold text-ink-900">{{ __('advisor.title') }}</p>

        <div class="ms-auto flex items-center gap-1.5">
            @foreach ($steps as $index => $meta)
                <button type="button"
                        wire:click="goTo({{ $index }})"
                        class="size-2.5 rounded-full transition-colors
                               {{ filled($meta['value']) ? 'bg-brand-600' : ($step === $index ? 'bg-accent-400' : 'bg-ink-200') }}"
                        aria-label="{{ __('advisor.step', ['number' => $index]) }}"></button>
            @endforeach
            <span class="tabular ms-1.5 text-xs text-ink-400">{{ $answered }}/4</span>
        </div>
    </div>

    <div class="p-5 sm:p-7">
        @if ($result)
            {{-- ---------------------------------------------------------- --}}
            {{-- Result                                                     --}}
            {{-- ---------------------------------------------------------- --}}
            <div class="flex flex-wrap items-baseline justify-between gap-3">
                <h3 class="text-lg font-black text-ink-900">{{ __('advisor.result_title') }}</h3>
                <button type="button" wire:click="reset_" class="btn-ghost btn-sm">
                    {{ __('advisor.start_over') }}
                </button>
            </div>

            {{-- The brief, read back. A recommendation you cannot check is a guess. --}}
            <p class="mt-2 text-sm leading-7 text-ink-500">
                {{ __('advisor.brief', [
                    'surface' => $surface ? __('enums.surface_type.'.$surface) : '—',
                    'soil' => $soil ? __('enums.soil_type.'.$soil) : '—',
                    'area' => $areaBand ? __('advisor.area.'.$areaBand) : '—',
                ]) }}
                @if ($result['required_productivity'] > 0)
                    <span class="tabular font-semibold text-ink-700">
                        {{ __('advisor.required', ['value' => number_format($result['required_productivity'])]) }}
                    </span>
                @endif
            </p>

            @if ($result['relaxed'])
                <p class="mt-3 flex items-start gap-2 rounded-lg bg-accent-50 px-3.5 py-2.5 text-xs leading-6 text-ink-700">
                    <x-ui-icon name="check-circle" class="mt-0.5 size-4 shrink-0 text-accent-600" />
                    {{ __('advisor.relaxed_note') }}
                </p>
            @endif

            @if ($result['best'])
                {{--
                    The card alone is an assertion. Beside it, the reasons —
                    surface, soil, the productivity arithmetic — are what turn
                    it into something a buyer can check and defend internally,
                    which is exactly what they have to do with it.
                --}}
                <div class="mt-6">
                    <p class="eyebrow mb-3 text-accent-600">{{ __('advisor.best_match') }}</p>

                    <div class="grid gap-6 lg:grid-cols-5 lg:items-start">
                        <div class="lg:col-span-2">
                            <x-product-card :product="$result['best']" />
                        </div>

                        <div class="rounded-[var(--radius-card)] border border-ink-100 bg-ink-50/70 p-5 lg:col-span-3">
                            <p class="text-sm font-bold text-ink-900">{{ __('advisor.why_title') }}</p>

                            <dl class="mt-4 space-y-3.5">
                                @php
                                    $best = $result['best'];
                                    $reasons = [];

                                    if ($surface) {
                                        $reasons[] = [__('advisor.why_surface'), __('enums.surface_type.'.$surface)];
                                    }

                                    if ($soil && ! $result['relaxed']) {
                                        $reasons[] = [__('advisor.why_soil'), __('enums.soil_type.'.$soil)];
                                    }

                                    if ($best->productivity_sqm_h && $result['required_productivity']) {
                                        $reasons[] = [
                                            __('advisor.why_productivity'),
                                            __('advisor.why_productivity_value', [
                                                'machine' => number_format((int) $best->productivity_sqm_h),
                                                'required' => number_format($result['required_productivity']),
                                            ]),
                                        ];
                                    }

                                    if ($frequency) {
                                        $reasons[] = [
                                            __('advisor.why_frequency'),
                                            __('advisor.frequency.'.$frequency).' · '.$best->power_source?->getLabel(),
                                        ];
                                    }
                                @endphp

                                @foreach ($reasons as [$label, $value])
                                    <div class="flex items-start gap-3">
                                        <x-ui-icon name="check-circle" class="mt-0.5 size-4 shrink-0 text-brand-600" />
                                        <div>
                                            <dt class="text-xs text-ink-400">{{ $label }}</dt>
                                            <dd class="mt-0.5 text-sm leading-6 font-semibold text-ink-800">{{ $value }}</dd>
                                        </div>
                                    </div>
                                @endforeach
                            </dl>

                            <div class="mt-5 flex flex-wrap gap-2.5 border-t border-ink-200/70 pt-4">
                                <a href="{{ $best->url() }}" class="btn-primary btn-sm">{{ __('ui.view_details') }}</a>
                                <a href="{{ route('compare') }}" class="btn-outline btn-sm">{{ __('ui.compare') }}</a>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($result['alternatives']->isNotEmpty())
                    <div class="mt-8">
                        <p class="eyebrow mb-3">{{ __('advisor.alternatives') }}</p>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($result['alternatives'] as $product)
                                <x-product-card :product="$product" wire:key="adv-{{ $product->id }}" />
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- The call. Everything above is the reason to take it. --}}
                <div class="mt-8 rounded-[var(--radius-card)] border border-ink-100 bg-ink-50/70 p-5">
                    @if ($submitted)
                        <p class="flex items-center justify-center gap-2 py-2 text-sm font-semibold text-emerald-700">
                            <x-ui-icon name="check-circle" class="size-5" />
                            {{ __('ui.selector.thanks') }}
                        </p>
                    @elseif ($showLeadForm)
                        <form wire:submit="requestCall" class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <label for="adv-name" class="field-label">{{ __('ui.form.name') }}</label>
                                <input id="adv-name" type="text" class="field" wire:model="name" required>
                                @error('name') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="adv-phone" class="field-label">{{ __('ui.form.phone') }}</label>
                                <input id="adv-phone" type="tel" dir="ltr" class="field tabular text-start" wire:model="phone" required>
                                @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="adv-company" class="field-label">{{ __('ui.form.company') }}</label>
                                <input id="adv-company" type="text" class="field" wire:model="company">
                            </div>
                            <div class="sm:col-span-3">
                                <button type="submit" class="btn-accent btn-lg w-full sm:w-auto" wire:loading.attr="disabled">
                                    {{ __('advisor.request_call') }}
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-bold text-ink-900">{{ __('advisor.unsure_title') }}</p>
                                <p class="mt-1 text-sm leading-6 text-ink-500">{{ __('advisor.unsure_body') }}</p>
                            </div>
                            <button type="button" wire:click="$set('showLeadForm', true)" class="btn-primary btn-sm">
                                {{ __('advisor.request_call') }}
                            </button>
                        </div>
                    @endif
                </div>
            @else
                <x-empty-state :title="__('advisor.no_match')"
                               :href="route('contact')"
                               :link-label="__('ui.free_consultation')"
                               icon="wrench" />
            @endif
        @else
            {{-- ---------------------------------------------------------- --}}
            {{-- Questions                                                  --}}
            {{-- ---------------------------------------------------------- --}}
            <p class="text-base font-bold text-ink-900 sm:text-lg">{{ __('advisor.q'.$step) }}</p>

            <div class="mt-5 flex flex-wrap gap-2.5">
                @php $current = $steps[$step]; @endphp

                @foreach ($current['options'] as $option)
                    @php
                        $value = is_string($option) ? $option : $option->value;
                        $label = match ($step) {
                            1 => __('enums.surface_type.'.$value),
                            2 => __('enums.soil_type.'.$value),
                            3 => __('advisor.area.'.$value),
                            default => __('advisor.frequency.'.$value),
                        };
                        $selected = $current['value'] === $value;
                    @endphp

                    {{-- 48px tall: these are the only controls on the block, and most taps land here. --}}
                    <button type="button"
                            wire:click="choose('{{ $current['field'] }}', '{{ $value }}')"
                            class="flex min-h-12 items-center gap-2 rounded-xl border px-4 text-sm font-semibold transition
                                   {{ $selected
                                        ? 'border-brand-600 bg-brand-600 text-white shadow-[var(--shadow-e1)]'
                                        : 'border-ink-200 bg-white text-ink-700 hover:border-brand-300 hover:bg-brand-50' }}">
                        @if ($selected)
                            <x-ui-icon name="check" class="size-4" />
                        @endif
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="mt-7 flex flex-wrap items-center gap-3 border-t border-ink-100 pt-5">
                @if ($step > 1)
                    <button type="button" wire:click="back" class="btn-ghost btn-sm">
                        <x-ui-icon name="arrow-right" class="size-4 flip-rtl" />
                        {{ __('ui.previous') }}
                    </button>
                @endif

                @if ($step < 4)
                    <button type="button" wire:click="goTo({{ $step + 1 }})" class="btn-outline btn-sm">
                        {{ __('advisor.skip') }}
                    </button>
                @endif

                <label class="check-row ms-auto text-sm">
                    <input type="checkbox" wire:model="rentalOnly" class="check-box">
                    {{ __('ui.selector.rental_only') }}
                </label>

                {{-- Always available: someone who knows the answer to one question should not have to answer four. --}}
                <button type="button" wire:click="find" class="btn-accent btn-lg w-full sm:w-auto"
                        wire:loading.attr="disabled" wire:target="find">
                    <x-ui-icon name="search" class="size-4" />
                    {{ __('advisor.find') }}
                </button>
            </div>
        @endif
    </div>
</div>
