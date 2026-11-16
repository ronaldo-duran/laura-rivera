@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Paginación">
        @if ($paginator->onFirstPage())
            <span class="page-link is-disabled">← Anteriores</span>
        @else
            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">← Anteriores</a>
        @endif
        <span class="page-status">Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())
            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguientes →</a>
        @else
            <span class="page-link is-disabled">Siguientes →</span>
        @endif
    </nav>
@endif
