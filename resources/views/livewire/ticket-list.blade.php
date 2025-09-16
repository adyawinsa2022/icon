<div wire:poll.30s>
    <style>
        .nav-pills-sm .nav-link {
            padding: 0.25rem 0.75rem;
            font-size: 0.85rem;
            border-radius: 38px;
        }
    </style>

    <div class="d-flex flex-row align-items-center mb-2">
        <span class="fw-bold fs-5">{{ $title }}</span>
        @if ($userProfile == 'Technician' || $userProfile == 'Super-Admin')
            <button type="button" id="scanBtn" class="btn btn-primary ms-auto">
                <i class="bi bi-qr-code-scan"></i>
            </button>
        @endif
    </div>

    {{-- Nav Pills untuk memilih form --}}
    @if (in_array($userProfile, ['Technician', 'Super-Admin']) && !$deviceName)
        <ul class="nav nav-pills nav-pills-sm mb-3">
            <li class="nav-item">
                <a wire:click="$set('status', 'notold')" class="nav-link {{ $status === 'notold' ? 'active' : '' }}"
                    href="#">
                    Belum Selesai
                </a>
            </li>
            <li class="nav-item">
                <a wire:click="$set('status', 'all')" class="nav-link {{ $status == 'all' ? 'active' : '' }}"
                    href="#">
                    Semua
                </a>
            </li>
        </ul>
    @endif

    {{-- Daftar Tiket dalam bentuk Card --}}
    @if (count($tickets) === 0)
        <p>Tidak ada tiket.</p>
    @else
        @foreach ($tickets as $ticket)
            <a wire:click.prevent="openTicket({{ $ticket['id'] }})" href="#"
                class="card text-decoration-none text-dark mb-2 shadow-sm rounded-4">
                {{-- Titik merah notif --}}
                @if ($ticket['notif'])
                    <span
                        class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle">
                        <span class="visually-hidden">New alerts</span>
                    </span>
                @endif
                <div class="card-body">
                    <h5 class="card-title">{{ $ticket['name'] }}</h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <span
                            class="badge 
                            @if ($ticket['status'] == 'Baru') bg-success
                            @elseif($ticket['status'] == 'Proses') bg-primary
                            @elseif($ticket['status'] == 'Tunda') bg-warning
                            @elseif($ticket['status'] == 'Selesai') bg-secondary
                            @elseif($ticket['status'] == 'Tutup') bg-dark
                            @else bg-info @endif">{{ $ticket['status'] }}</span>
                        <small>{{ $ticket['date_mod'] }}</small>
                    </div>
                </div>
            </a>
        @endforeach
    @endif

    {{-- Pagination --}}
    <nav class="d-flex flex-column align-items-center">
        <span>
            Menampilkan {{ $tickets->firstItem() }} sampai {{ $tickets->lastItem() }} dari {{ $tickets->total() }}
            tiket
        </span>
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
                    <li class="page-item {{ $i == $page ? 'active' : '' }}">
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
