{{--
    Direct line to a consultant. Sits opposite the comparison strip so the two
    floating elements never overlap.
--}}
<a href="https://wa.me/{{ config('site.whatsapp') }}?text={{ rawurlencode(__('ui.whatsapp_chat')) }}"
   target="_blank"
   rel="noopener noreferrer"
   class="fixed bottom-6 end-5 z-40 flex items-center gap-2 rounded-full bg-[#25D366] py-3 ps-4 pe-5 text-sm font-semibold text-white shadow-lg transition-transform duration-200 hover:scale-105"
   aria-label="{{ __('ui.whatsapp_chat') }}">
    <x-ui-icon name="whatsapp" class="size-5" />
    <span class="hidden sm:inline">{{ __('ui.whatsapp_chat') }}</span>
</a>
