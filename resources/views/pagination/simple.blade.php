@if ($paginator->hasPages())
    <nav>
        @if ($paginator->onFirstPage())
            <span>&laquo; Prev</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}">&laquo; Prev</a>
        @endif

        @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
            @if ($page == $paginator->currentPage())
                <span class="active">{{ $page }}</span>
            @else
                <a href="{{ $url }}">{{ $page }}</a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}">Next &raquo;</a>
        @else
            <span>Next &raquo;</span>
        @endif
    </nav>
@endif
