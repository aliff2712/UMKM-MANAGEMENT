@if ($paginator->hasPages())
    <nav class="umora-pagination" aria-label="Navigasi Halaman">
        <ul class="umora-pagination-list">

            {{-- Tombol Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <li class="umora-page-item disabled">
                    <span class="umora-page-btn" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </span>
                </li>
            @else
                <li class="umora-page-item">
                    <a class="umora-page-btn" href="{{ $paginator->previousPageUrl() }}" aria-label="Halaman Sebelumnya">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </a>
                </li>
            @endif

            {{-- Nomor Halaman --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="umora-page-item disabled">
                        <span class="umora-page-btn umora-page-ellipsis">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="umora-page-item active" aria-current="page">
                                <span class="umora-page-btn umora-page-active">{{ $page }}</span>
                            </li>
                        @else
                            <li class="umora-page-item">
                                <a class="umora-page-btn" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Tombol Berikutnya --}}
            @if ($paginator->hasMorePages())
                <li class="umora-page-item">
                    <a class="umora-page-btn" href="{{ $paginator->nextPageUrl() }}" aria-label="Halaman Berikutnya">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </a>
                </li>
            @else
                <li class="umora-page-item disabled">
                    <span class="umora-page-btn" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </span>
                </li>
            @endif

        </ul>
    </nav>
@endif
