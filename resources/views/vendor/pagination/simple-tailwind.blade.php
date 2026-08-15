@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex justify-between gap-3">
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
    </nav>
@endif
