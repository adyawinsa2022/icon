<div>
    <h5 class="fw-bold mb-2">Detail Tiket</h5>
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">{{ $ticket['name'] ?? 'Tanpa Judul' }}</h5>
            <table class="table table-borderless">
                <tbody>
                    <tr>
                        <td class="fw-semibold">Pembuat</td>
                        <td>{{ $ticket['requesterName'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Dibuat</td>
                        <td>{{ $ticket['date_creation'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Bertugas</td>
                        <td>{{ $assignedTechs ? implode(', ', $assignedTechs) : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Status</td>
                        <td>
                            <span
                                class="badge 
                                @if ($ticket['statusName'] == 'Baru') bg-success
                                @elseif($ticket['statusName'] == 'Proses') bg-primary
                                @elseif($ticket['statusName'] == 'Tunda') bg-warning
                                @elseif($ticket['statusName'] == 'Selesai') bg-secondary
                                @elseif($ticket['statusName'] == 'Tutup') bg-dark
                                @else bg-info @endif">
                                {{ $ticket['statusName'] }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Perangkat</td>
                        <td>{{ $ticket['itemName'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Kategori</td>
                        <td>{{ $ticket['categoryName'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Lokasi</td>
                        <td>{!! html_entity_decode($ticket['locationName'] ?? '-') !!}</td>
                    </tr>
                </tbody>
            </table>


            <p class="mt-3">{!! html_entity_decode($ticket['content'] ?? '-') !!}</p>

            @if ($userProfile != 'User' && !in_array($userName, $assignedTechs) && $ticket['status'] < 5)
                <div class="d-flex justify-content-center">
                    <button wire:click="takeTicket" class="btn btn-primary">
                        Ambil Tiket
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
