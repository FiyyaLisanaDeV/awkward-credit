@if ($paginator->hasPages())
    <div class="pagination">
        @if ($paginator->onFirstPage())
            <span class="btn btn-sm disabled">‹ {{ __('form.prev') }}</span>
        @else
            <a class="btn btn-sm" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ {{ __('form.prev') }}</a>
        @endif
        <span class="hint">{{ __('form.page') }} {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())
            <a class="btn btn-sm" href="{{ $paginator->nextPageUrl() }}" rel="next">{{ __('form.next') }} ›</a>
        @else
            <span class="btn btn-sm disabled">{{ __('form.next') }} ›</span>
        @endif
    </div>
@endif
