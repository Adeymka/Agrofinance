@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <button type="button" disabled aria-disabled="true" class="pagination-btn pagination-btn-disabled">
                ← Précédente
            </button>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-btn">
                ← Précédente
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="pagination-dots">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="pagination-btn pagination-btn-active">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" class="pagination-btn">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-btn">
                Suivante →
            </a>
        @else
            <button type="button" disabled aria-disabled="true" class="pagination-btn pagination-btn-disabled">
                Suivante →
            </button>
        @endif
    </nav>
@endif

<style>
    .pagination-btn {
        font-family: var(--font-ui), sans-serif;
        font-size: 12px;
        padding: 6px 12px;
        margin: 0 2px;
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.08);
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        cursor: pointer;
        transition: all 0.15s;
        display: inline-block;
    }
    .pagination-btn:hover {
        background: rgba(255, 255, 255, 0.12);
        color: rgba(255, 255, 255, 0.9);
        border-color: rgba(255, 255, 255, 0.3);
    }
    .pagination-btn-active {
        background: var(--af-color-accent);
        color: #fff;
        border-color: var(--af-color-accent);
    }
    .pagination-btn-disabled {
        opacity: 0.4;
        cursor: not-allowed;
        background: rgba(255, 255, 255, 0.05);
    }
    .pagination-dots {
        color: rgba(255, 255, 255, 0.4);
        padding: 0 4px;
    }
</style>
