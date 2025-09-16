<div wire:poll.30s="getAttentionTicket">
    @if ($tickets['solved'] || $tickets['active'])
        <h5 class="fw-bold mb-2">Ada yang perlu perhatian nih!</h5>
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                @if (!in_array($userProfile, ['Technician', 'Super-Admin']))
                    @if ($tickets['solved'])
                        <div class="mb-3">
                            <h6 class="mb-1">Tiket Selesai</h6>
                            <small class="text-muted mb-1 d-block">Tiket ini perlu persetujuan kamu.</small>
                            <!-- Card kecil angka -->
                            <div class="card text-center shadow-sm">
                                <button class="btn" data-bs-toggle="modal" data-bs-target="#ticketsModal"
                                    data-bs-category="solved">
                                    <h3 class="mb-0">{{ count($tickets['solved']) }}</h3>
                                </button>
                            </div>
                        </div>
                    @endif
                @endif
                @if ($tickets['active'])
                    <div class="mb-3">
                        <h6 class="mb-1">Tiket Aktif</h6>
                        <small class="text-muted mb-1 d-block">Ayo selesaikan masalah ini.</small>
                        <!-- Card kecil angka -->
                        <div class="card text-center shadow-sm">
                            <button class="btn" data-bs-toggle="modal" data-bs-target="#ticketsModal"
                                data-bs-category="active">
                                <h3 class="mb-0">{{ count($tickets['active']) }}</h3>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Modal --}}
        <div wire:ignore class="modal fade" id="ticketsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content" style="max-height: 75%;">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ticketsModalTitle">Daftar Tiket</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="ticketsModalBody">
                        <p class="text-center text-muted">Memuat tiket...</p>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ticketsModal = document.getElementById('ticketsModal');

                ticketsModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget; // tombol yang diklik
                    const category = button.getAttribute('data-bs-category'); // 'active' atau 'solved'

                    // Ambil data dari Livewire
                    const tickets = @json($tickets);

                    const modalTitle = ticketsModal.querySelector('#ticketsModalTitle');
                    const modalBody = ticketsModal.querySelector('#ticketsModalBody');

                    // Set judul modal sesuai kategori
                    if (category === 'active') {
                        modalTitle.textContent = 'Daftar Tiket Aktif';
                    } else if (category === 'solved') {
                        modalTitle.textContent = 'Daftar Tiket Selesai';
                    }

                    const ticketShowUrl = "{{ url('ticket') }}";

                    // Render list tiket
                    const list = tickets[category] || [];
                    if (list.length === 0) {
                        modalBody.innerHTML = '<p class="text-center text-muted">Tidak ada tiket.</p>';
                    } else {
                        let html = '';
                        list.forEach(ticket => {
                            html +=
                                `<a href="${ticketShowUrl}/${ticket.id}"
                                class="card text-decoration-none text-dark mb-2 shadow-sm rounded-4" >
                                <div class="card-body">
                                    <h5 class="card-title">${ticket.name}</h5>
                                    <div class="d-flex justify-content-end">
                                        <small>Dibuat: ${ticket.date}</small>
                                    </div>
                                </div>
                            </a>`;
                        });
                        modalBody.innerHTML = html;
                    }
                });
            });
        </script>
    @endif
</div>
