<x-layout title="Detail Tiket">
    <div class="container pt-3">
        <!-- Info Tiket -->
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
                        <a href="{{ route('ticket.take', $ticket['id']) }}" class="btn btn-primary">Ambil Tiket</a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Timeline Aktivitas -->
        <h5 class="fw-bold mb-2">Riwayat Aktivitas</h5>
        @forelse($activities as $act)
            @php
                $isAssignedTech = in_array($act['author_name'], $assignedTechs ?? []);
                $cardRadius = $isAssignedTech ? '38px 38px 0 38px' : '38px 38px 38px 0px';
            @endphp
            <div class="card mb-2 shadow-sm" style="border-radius: {{ $cardRadius }};">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span
                            class="badge 
                            @if ($act['type'] == 'followup') bg-primary
                            @elseif($act['type'] == 'solution') bg-success
                            @elseif($act['type'] == 'task') bg-warning text-dark
                            @elseif($act['type'] == 'document') bg-info text-dark
                            @else bg-secondary @endif">
                            {{ ucfirst($act['type']) }}
                        </span>
                        <small class="text-muted">{{ $act['date'] }}</small>
                    </div>
                    <p class="mb-1 small fw-semibold">{{ $act['author_name'] ?? '-' }}</p>
                    <div class="small">{!! html_entity_decode($act['content'] ?? '<i>[Kosong]</i>') !!}</div>

                    @if ($act['type'] == 'task' && $act['begin'])
                        <div class="mt-2 small text-muted">
                            <i class="bi bi-calendar-event"></i> :
                            {{ $act['begin'] }} <i class="bi bi-arrow-right"></i> {{ $act['end'] }}
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-muted">Belum ada aktivitas pada tiket ini.</p>
        @endforelse

        {{-- Form Tambah Aktivitas --}}
        @if ($ticket['status'] != 6)
            @if ($userProfile != 'User' && $ticket['status'] < 5 && in_array($userName, $assignedTechs))
                {{-- Nav Pills untuk memilih form --}}
                <ul class="nav nav-pills my-3" id="ticketFormTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="followup-tab" data-bs-toggle="pill"
                            data-bs-target="#followup" type="button" role="tab">
                            Followup
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="task-tab" data-bs-toggle="pill" data-bs-target="#task"
                            type="button" role="tab">
                            Task
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="solution-tab" data-bs-toggle="pill" data-bs-target="#solution"
                            type="button" role="tab">
                            Solution
                        </button>
                    </li>
                </ul>

                {{-- Isi masing-masing tab form --}}
                <div class="tab-content" id="ticketFormTabsContent">
                    {{-- FORM FOLLOWUP --}}
                    <div class="tab-pane fade show active" id="followup" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <form action="{{ route('ticket.followup', $ticket['id']) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <textarea class="form-control" name="content" rows="3" placeholder="Tambah Followup..."></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-primary" type="submit">Kirim Followup</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- FORM TASK --}}
                    <div class="tab-pane fade" id="task" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <form action="{{ route('ticket.task', $ticket['id']) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <textarea class="form-control" name="content" rows="3" placeholder="Tambah Task..."></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1"
                                                id="planCheckbox" name="planCheckbox">
                                            <label class="form-check-label" for="planCheckbox">
                                                Jadwalkan
                                            </label>
                                        </div>
                                    </div>
                                    <div id="plannedTask" class="d-none">
                                        <div class="mb-3">
                                            <label for="begin" class="form-label">Waktu Mulai</label>
                                            <input type="datetime-local" id="begin" name="begin"
                                                class="form-control">
                                        </div>
                                        <div class="mb-3">
                                            <label for="duration" class="form-label">Durasi</label>
                                            <div class="d-flex gap-2">
                                                <select name="duration_hours" class="form-select">
                                                    @for ($h = 0; $h <= 23; $h++)
                                                        <option value="{{ $h }}">{{ $h }} jam
                                                        </option>
                                                    @endfor
                                                </select>

                                                <select name="duration_minutes" class="form-select">
                                                    @for ($m = 0; $m < 60; $m += 5)
                                                        <option value="{{ $m }}">{{ $m }} menit
                                                        </option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-warning" type="submit">Kirim Task</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- FORM SOLUTION --}}
                    <div class="tab-pane fade" id="solution" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <form action="{{ route('ticket.solution', $ticket['id']) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <textarea class="form-control" name="content" rows="3" placeholder="Tambah Solusi..."></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-success" type="submit">Kirim Solusi</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                @if ($ticket['is_requester'])
                    @if ($ticket['status'] == 5)
                        {{-- Form Approval --}}
                        <div class="card mt-4 shadow-sm">
                            <div class="card-body">
                                <form
                                    action="{{ route('ticket.approval', [$ticket['id'], $ticket['lastSolutionId']]) }}"
                                    method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Tiket ini telah diselesaikan oleh
                                            Teknisi.
                                            Apakah anda menyetujuinya?</label>
                                        <textarea class="form-control" id="content" name="content" rows="3" placeholder="Komentar (Opsional)"></textarea>
                                    </div>
                                    <div class="d-flex justify-content-center gap-3">
                                        <input type="hidden" name="answer" id="answer" value="approve" />
                                        <button class="btn btn-success" type="submit"
                                            onclick="document.getElementById('answer').value='approve'">
                                            Ya, setuju
                                        </button>
                                        <button class="btn btn-danger" type="submit"
                                            onclick="document.getElementById('answer').value='refuse'">
                                            Tidak setuju
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        {{-- Form Followup --}}
                        <div class="card mt-4 shadow-sm">
                            <div class="card-body">
                                <form action="{{ route('ticket.followup', $ticket['id']) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <textarea class="form-control" name="content" rows="3" placeholder="Tambah Followup..."></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-primary" type="submit">Kirim</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                @endif
            @endif
        @endif
    </div>
    <script>
        const checkbox = document.getElementById('planCheckbox')
        const scheduleFields = document.getElementById('plannedTask')
        checkbox.addEventListener('change', function() {
            scheduleFields.classList.toggle('d-none', !this.checked)
        });
    </script>
</x-layout>
