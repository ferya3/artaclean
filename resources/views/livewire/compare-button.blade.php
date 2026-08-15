<button type="button"
        wire:click="toggle"
        @class([
            'btn btn-sm shrink-0 transition-colors',
            'border border-ink-200 text-ink-600 hover:border-ink-900 hover:text-ink-900' => ! $active,
            'border border-brand-600 bg-brand-50 text-brand-700' => $active,
            '!px-3' => $compact,
        ])
        :aria-pressed="'{{ $active ? 'true' : 'false' }}'"
        title="{{ $active ? __('ui.in_compare') : __('ui.add_to_compare') }}">
    <x-ui-icon :name="$active ? 'check' : 'scale'" class="size-4" />
    @unless ($compact)
        <span>{{ $active ? __('ui.in_compare') : __('ui.add_to_compare') }}</span>
    @endunless
</button>
