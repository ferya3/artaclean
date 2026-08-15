@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}"
         class="flex items-center justify-between gap-4">
        {{-- Mobile: previous / next only --}}
        <div class="flex flex-1 justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="btn-outline btn-sm cursor-default opacity-50">{{ __('pagination.previous') }}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn-outline btn-sm">{{ __('pagination.previous') }}</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn-outline btn-sm">{{ __('pagination.next') }}</a>
            @else
                <span class="btn-outline btn-sm cursor-default opacity-50">{{ __('pagination.next') }}</span>
            @endif
        </div>

        <div class="hidden w-full items-center justify-between sm:flex">
            <p class="tabular text-xs text-ink-500">
                {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} / {{ $paginator->total() }}
            </p>

            <div class="flex items-center gap-1">
                @if ($paginator->onFirstPage())
                    <span class="btn-outline btn-sm size-9 cursor-default !p-0 opacity-40" aria-disabled="true">
                        <x-ui-icon name="arrow-right" class="size-4 flip-rtl" />
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn-outline btn-sm size-9 !p-0"
                       aria-label="{{ __('pagination.previous') }}">
                        <x-ui-icon name="arrow-right" class="size-4 flip-rtl" />
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-2 text-sm text-ink-400">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="tabular btn btn-sm size-9 !p-0 bg-ink-900 text-white" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="tabular btn-outline btn-sm size-9 !p-0">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn-outline btn-sm size-9 !p-0"
                       aria-label="{{ __('pagination.next') }}">
                        <x-ui-icon name="arrow-left" class="size-4 flip-rtl" />
                    </a>
                @else
                    <span class="btn-outline btn-sm size-9 cursor-default !p-0 opacity-40" aria-disabled="true">
                        <x-ui-icon name="arrow-left" class="size-4 flip-rtl" />
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
