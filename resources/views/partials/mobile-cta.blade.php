{{--
    The phone's version of the header's consultation button.

    On a desktop the bar at the top is always a flick of the mouse away; on a
    phone it is hidden until the visitor scrolls and then sits above the fold
    of a page they are reading downwards. For equipment at this price the three
    things they actually want are always the same — help choosing, a price, and
    a human — so those three sit within thumb reach instead.

    Hidden from `lg:` up, where the header bar does the job.
--}}
<div class="fixed inset-x-0 bottom-0 z-40 border-t border-ink-200 bg-white/95 backdrop-blur lg:hidden"
     style="padding-bottom: env(safe-area-inset-bottom, 0px)">
    <div class="grid grid-cols-[1fr_1fr_auto] items-stretch gap-1.5 p-2">
        <a href="{{ route('advisor') }}"
           class="flex min-h-12 items-center justify-center gap-1.5 rounded-lg bg-ink-50 px-2 text-sm font-bold text-ink-800">
            <x-ui-icon name="sparkles" class="size-4 shrink-0 text-brand-700" />
            {{ __('nav.advisor') }}
        </a>

        <a href="{{ route('contact') }}"
           class="flex min-h-12 items-center justify-center gap-1.5 rounded-lg bg-accent-400 px-2 text-sm font-bold text-ink-950">
            {{ __('ui.request_price') }}
        </a>

        <a href="tel:{{ config('site.phone_e164') }}"
           class="grid min-h-12 w-12 place-items-center rounded-lg bg-ink-900 text-white"
           aria-label="{{ __('ui.call_us') }}">
            <x-ui-icon name="phone" class="size-5" />
        </a>
    </div>
</div>
