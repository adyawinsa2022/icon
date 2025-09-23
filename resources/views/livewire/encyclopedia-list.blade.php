<div>
    <h5 class="fw-bold mb-3">Ensiklopedia</h5>
    <div class="mb-3">
        <form class="d-flex flex-row gap-2">
            <input type="text" class="form-control" id="searchEncyclopedia" wire:model.live.debounce.500ms="search"
                placeholder="Mau cari apa hari ini?">
        </form>
    </div>
    @if (count($articles) === 0)
        <p>Tidak ada artikel yang ditemukan.</p>
    @else
        <div wire:key="articles-list-{{ $page }}">
            @foreach ($articles as $article)
                <a href="{{ route('encyclopedia.article', $article['id']) }}"
                    class="card text-decoration-none text-dark mb-2 shadow-sm rounded-4">
                    <div class="card-body py-2 d-flex justify-content-between align-items-center">
                        <span>{{ $article['subject'] }}</span>
                        <div class="d-flex align-items-center gap-3">
                            <span class="text-nowrap"><i class="bi bi-eye"></i> {{ $article['view'] }}</span>
                            <i class="bi bi-chevron-right"></i>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Pagination --}}
    <nav class="d-flex flex-column align-items-center">
        @if (count($articles) > 0)
            <span>
                Menampilkan {{ $articles->firstItem() }} sampai {{ $articles->lastItem() }} dari
                {{ $articles->total() }}
                artikel
            </span>
        @endif
        @if ($totalPages > 1)
            <ul class="pagination p-0">
                {{-- Tombol ke halaman pertama --}}
                <li class="page-item {{ $page == 1 ? 'disabled' : '' }}">
                    <a class="page-link" href="#" wire:click.prevent="gotoPage(1)">
                        <i class="bi bi-chevron-double-left"></i>
                    </a>
                </li>

                {{-- Tombol Previous --}}
                <li class="page-item {{ $page == 1 ? 'disabled' : '' }}">
                    <a class="page-link" href="#" wire:click.prevent="previousPage">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>

                {{-- Ellipsis sebelum angka jika halaman jauh dari awal --}}
                @if ($page > 3)
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                @endif

                {{-- Halaman di sekitar halaman aktif (2 sebelum & 2 sesudah) --}}
                @for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++)
                    <li class="page-item {{ $i == $page ? 'active' : '' }}" wire:key="page-{{ $i }}">
                        <a class="page-link" href="#"
                            wire:click.prevent="gotoPage({{ $i }})">{{ $i }}</a>
                    </li>
                @endfor

                {{-- Ellipsis setelah angka jika masih ada halaman yang belum ditampilkan --}}
                @if ($page + 3 <= $totalPages)
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                @endif

                {{-- Tombol Next --}}
                <li class="page-item {{ $page == $totalPages ? 'disabled' : '' }}">
                    <a class="page-link" href="#" wire:click.prevent="nextPage">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>

                {{-- Tombol ke halaman terakhir --}}
                <li class="page-item {{ $page == $totalPages ? 'disabled' : '' }}">
                    <a class="page-link" href="#" wire:click.prevent="gotoPage({{ $totalPages }})">
                        <i class="bi bi-chevron-double-right"></i>
                    </a>
                </li>
            </ul>
        @endif
    </nav>
</div>
