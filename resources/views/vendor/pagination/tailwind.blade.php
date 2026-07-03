@if ($paginator->hasPages())
    <nav class="flex flex-col gap-3 rounded-lg border border-white/10 bg-slate-950/45 px-3 py-3 sm:flex-row sm:items-center sm:justify-between" role="navigation" aria-label="{{ __('Pagination Navigation') }}">
        <p class="text-xs font-medium text-slate-400">
            @if ($paginator->firstItem())
                {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} de {{ $paginator->total() }}
            @else
                {{ $paginator->count() }} de {{ $paginator->total() }}
            @endif
        </p>

        <div class="flex flex-wrap items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-lg border border-white/5 bg-white/5 text-slate-600" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                    <span aria-hidden="true">‹</span>
                </span>
            @else
                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-slate-300 transition-colors hover:border-emerald-300/35 hover:bg-emerald-400/10 hover:text-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-300/25 disabled:pointer-events-none disabled:opacity-50" aria-label="{{ __('pagination.previous') }}">
                    <span aria-hidden="true">‹</span>
                </button>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-white/5 bg-white/5 px-3 text-sm text-slate-500">
                        {{ $element }}
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-emerald-300/35 bg-emerald-400/15 px-3 text-sm font-semibold text-emerald-100" aria-current="page">
                                {{ $page }}
                            </span>
                        @else
                            <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-white/10 bg-white/5 px-3 text-sm font-medium text-slate-300 transition-colors hover:border-emerald-300/35 hover:bg-emerald-400/10 hover:text-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-300/25 disabled:pointer-events-none disabled:opacity-50" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-slate-300 transition-colors hover:border-emerald-300/35 hover:bg-emerald-400/10 hover:text-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-300/25 disabled:pointer-events-none disabled:opacity-50" aria-label="{{ __('pagination.next') }}">
                    <span aria-hidden="true">›</span>
                </button>
            @else
                <span class="inline-flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-lg border border-white/5 bg-white/5 text-slate-600" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                    <span aria-hidden="true">›</span>
                </span>
            @endif
        </div>
    </nav>
@endif
