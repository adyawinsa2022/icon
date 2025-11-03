<div wire:poll.30s="getStatsTicket">
    <h5 class="fw-bold mb-2">Statistik Tiket</h5>
    <div class="card mb-3 shadow-sm">
        <div class="col-12 col-md-4 p-2 d-flex flex-row gap-2">
            <select wire:model.live="range" id="range" class="form-select">
                <option value="day">Hari</option>
                <option value="week">Minggu</option>
                <option value="month">Bulan</option>
                <option value="all">Semua</option>
            </select>

            <input id="rangeValue" type={{ $range == 'day' ? 'date' : $range }} wire:model.live="value"
                class="form-control {{ $range == 'all' ? 'd-none' : '' }}">
        </div>
        <div class="card-body">
            @if ($ticket['total'] > 0)
                <div class="row d-flex flex-column justify-content-center align-items-center mb-3">
                    <span class="position-absolute text-center fs-1 fw-bold counter"
                        data-target="{{ $ticket['total'] ?? 0 }}">0</span>
                    <div style="width: 300px; height: 300px; padding: 0;">
                        <canvas id="ticketPieChart"></canvas>
                    </div>
                </div>
            @else
                <p class="text-center">Tidak ada Tiket</p>
            @endif
            <div class="row justify-content-evenly mb-3">
                <!-- Total -->
                <div class="col-3 px-2">
                    <div class="d-flex flex-column py-3 rounded text-center shadow border border-black border-opacity-25"
                        style="background-color: white">
                        <span class="counter" data-target="{{ $ticket['total'] ?? 0 }}">0</span>
                        <span class="text-nowrap">Total</span>
                    </div>
                </div>

                <!-- Active -->
                <div class="col-3 px-2">
                    <div class="d-flex flex-column py-3 rounded text-center shadow text-white"
                        style="background-color: #0D6EFD;">
                        <span class="counter" data-target="{{ $ticket['active'] ?? 0 }}">0</span>
                        <span class="text-nowrap">Aktif</span>
                    </div>
                </div>

                <!-- Solved -->
                <div class="col-3 px-2">
                    <div class="d-flex flex-column py-3 rounded text-center shadow" style="background-color: #d8d9d9;">
                        <span class="counter" data-target="{{ $ticket['solved'] ?? 0 }}">0</span>
                        <span class="text-nowrap">Selesai</span>
                    </div>
                </div>

                <!-- Closed -->
                <div class="col-3 px-2">
                    <div class="d-flex flex-column py-3 rounded text-center shadow text-white"
                        style="background-color: #505051;">
                        <span class="counter" data-target="{{ $ticket['closed'] ?? 0 }}">0</span>
                        <span class="text-nowrap">Tutup</span>
                    </div>
                </div>
            </div>
            <div class="text-center">
                <a href="{{ route('ticket.index') }}">Lihat Semua <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/chart.js-4.5.0/chart.js') }}"></script>
    <script>
        let ticketChart = null;

        function createChart(dataTicket) {
            const canvas = document.getElementById('ticketPieChart');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');

            if (ticketChart) {
                ticketChart.destroy();
            }

            const data = {
                labels: ['Aktif', 'Selesai', 'Tutup'],
                datasets: [{
                    data: [
                        dataTicket.active ?? 0,
                        dataTicket.solved ?? 0,
                        dataTicket.closed ?? 0,
                    ],
                    backgroundColor: ['#0D6EFD', '#d8d9d9', '#505051'],
                    borderWidth: 1
                }]
            };

            ticketChart = new Chart(ctx, {
                type: 'doughnut', // <-- pakai doughnut agar tengahnya bolong
                data: data,
                options: {
                    cutout: '50%', // persentase bolongan tengah (default 50%)
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let value = context.raw;
                                    let total = context.dataset.data.reduce((a, b) => a + b,
                                        0);
                                    return `${context.label}: ${value}`;
                                }
                            }
                        }
                    }
                }
            });
        }

        let tickets = @json($ticket);

        createChart({
            active: tickets['active'] ?? 0,
            solved: tickets['solved'] ?? 0,
            closed: tickets['closed'] ?? 0,
        });

        document.addEventListener('livewire:initialized', function() {
            Livewire.on('ticketUpdated', data => {
                tickets = data.ticket;
            });

            Livewire.on('rangeChanged', range => {
                const input = document.getElementById('rangeValue');
                if (input) {
                    const inputType = range.value === 'day' ? 'date' : range.value;
                    input.type = inputType;
                }
            });

            Livewire.hook('morphed', () => {
                createChart({
                    active: tickets['active'] ?? 0,
                    solved: tickets['solved'] ?? 0,
                    closed: tickets['closed'] ?? 0,
                });
                generateNumberTickets();
            });
        });

        function generateNumberTickets() {
            const counters = document.querySelectorAll('.counter');
            const duration = 1000; // 1 detik animasi

            counters.forEach(counter => {
                const target = parseInt(counter.dataset.target, 10);
                const startTime = performance.now();

                function update(currentTime) {
                    const progress = Math.min((currentTime - startTime) / duration, 1);
                    const current = Math.floor(progress * target);

                    counter.textContent = current.toLocaleString(); // format ribuan

                    if (progress < 1) {
                        requestAnimationFrame(update);
                    }
                }

                requestAnimationFrame(update);
            });
        }
        generateNumberTickets();
    </script>
</div>
