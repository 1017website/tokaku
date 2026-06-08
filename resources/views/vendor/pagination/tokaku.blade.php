@if ($paginator->hasPages())
    @php
        $btnBase = 'display:inline-flex;align-items:center;justify-content:center;min-width:38px;height:38px;padding:0 12px;border-radius:10px;font-size:13.5px;font-weight:600;border:1px solid #e2e8f0;background:#fff;color:#0F6E56;text-decoration:none;transition:all .15s ease;';
        $btnActive = 'display:inline-flex;align-items:center;justify-content:center;min-width:38px;height:38px;padding:0 12px;border-radius:10px;font-size:13.5px;font-weight:700;border:1px solid #0F6E56;background:#0F6E56;color:#fff;text-decoration:none;box-shadow:0 2px 6px rgba(15,110,86,.25);';
        $btnDisabled = 'display:inline-flex;align-items:center;justify-content:center;min-width:38px;height:38px;padding:0 12px;border-radius:10px;font-size:13.5px;font-weight:600;border:1px solid #f1f5f9;background:#f8fafc;color:#cbd5e1;cursor:not-allowed;';
        $dots = 'display:inline-flex;align-items:center;justify-content:center;min-width:38px;height:38px;color:#94a3b8;font-size:13.5px;';
    @endphp

    <nav role="navigation" aria-label="Pagination" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <p style="font-size:12.5px;color:#64748b;margin:0;">
            Menampilkan
            <span style="font-weight:600;color:#0f172a;">{{ $paginator->firstItem() ?? 0 }}</span>
            –
            <span style="font-weight:600;color:#0f172a;">{{ $paginator->lastItem() ?? 0 }}</span>
            dari
            <span style="font-weight:600;color:#0f172a;">{{ $paginator->total() }}</span>
            hasil
        </p>

        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
            {{-- Prev --}}
            @if ($paginator->onFirstPage())
                <span style="{{ $btnDisabled }}" aria-disabled="true">‹</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" style="{{ $btnBase }}"
                   onmouseover="this.style.background='#f0fdf6';this.style.borderColor='#0F6E56';"
                   onmouseout="this.style.background='#fff';this.style.borderColor='#e2e8f0';">‹</a>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span style="{{ $dots }}">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span style="{{ $btnActive }}" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" style="{{ $btnBase }}"
                               onmouseover="this.style.background='#f0fdf6';this.style.borderColor='#0F6E56';"
                               onmouseout="this.style.background='#fff';this.style.borderColor='#e2e8f0';">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" style="{{ $btnBase }}"
                   onmouseover="this.style.background='#f0fdf6';this.style.borderColor='#0F6E56';"
                   onmouseout="this.style.background='#fff';this.style.borderColor='#e2e8f0';">›</a>
            @else
                <span style="{{ $btnDisabled }}" aria-disabled="true">›</span>
            @endif
        </div>
    </nav>
@endif
