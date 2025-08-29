<div>
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
            <a href="{{ route('ticket.show', $ticket['id']) }}"
                class="card text-decoration-none text-dark mb-2 shadow-sm rounded-4">
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
    @if ($totalPages > 1)
        <nav class="d-flex justify-content-center">
            <ul class="pagination">
                {{-- Tombol Previous --}}
                <li class="page-item {{ $page <= 1 ? 'disabled' : '' }}">
                    <a class="page-link" href="#" wire:click.prevent="previousPage">
                        <i class="bi bi-arrow-left-circle"></i>
                    </a>
                </li>

                {{-- Nomor halaman --}}
                @for ($i = 1; $i <= $totalPages; $i++)
                    <li class="page-item {{ $page == $i ? 'active' : '' }}">
                        <a class="page-link" href="#" wire:click.prevent="gotoPage({{ $i }})">
                            {{ $i }}
                        </a>
                    </li>
                @endfor

                {{-- Tombol Next --}}
                <li class="page-item {{ $page >= $totalPages ? 'disabled' : '' }}">
                    <a class="page-link" href="#" wire:click.prevent="nextPage">
                        <i class="bi bi-arrow-right-circle"></i>
                    </a>
                </li>
            </ul>
        </nav>
    @endif
</div>
