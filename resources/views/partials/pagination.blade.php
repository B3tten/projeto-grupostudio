@if ($paginator->hasPages())
    <nav aria-label="Paginação">
        <ul class="pagination">
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                @if ($paginator->onFirstPage())
                    <span class="page-link"><i class="bi bi-chevron-left"></i></span>
                @else
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Página anterior">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                @endif
            </li>

            @php
                $inicio = max(1, $paginator->currentPage() - 2);
                $fim = min($paginator->lastPage(), $paginator->currentPage() + 2);
            @endphp

            @if ($inicio > 1)
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
                </li>
                @if ($inicio > 2)
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                @endif
            @endif

            @for ($pagina = $inicio; $pagina <= $fim; $pagina++)
                <li class="page-item {{ $pagina === $paginator->currentPage() ? 'active' : '' }}">
                    @if ($pagina === $paginator->currentPage())
                        <span class="page-link">{{ $pagina }}</span>
                    @else
                        <a class="page-link" href="{{ $paginator->url($pagina) }}">{{ $pagina }}</a>
                    @endif
                </li>
            @endfor

            @if ($fim < $paginator->lastPage())
                @if ($fim < $paginator->lastPage() - 1)
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                @endif
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a>
                </li>
            @endif

            <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                @if ($paginator->hasMorePages())
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Próxima página">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                @else
                    <span class="page-link"><i class="bi bi-chevron-right"></i></span>
                @endif
            </li>
        </ul>
    </nav>
@endif
