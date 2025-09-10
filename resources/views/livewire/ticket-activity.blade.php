<div>
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
</div>
