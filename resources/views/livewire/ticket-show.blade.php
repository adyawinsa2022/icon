<div wire:poll.15s="refreshTicket">
    {{-- Bagian Detail Tiket --}}
    @livewire('ticket-detail', [
        'ticket' => $ticket,
        'assignedTechs' => $assignedTechs,
        'userProfile' => $userProfile,
        'userName' => $userName,
    ])

    {{-- Bagian Aktivitas Tiket --}}
    @livewire('ticket-activity', [
        'activities' => $activities,
        'assignedTechs' => $assignedTechs,
    ])

    {{-- Bagian Aktivitas Form Tiket --}}
    @livewire('ticket-activity-form', [
        'ticket' => $ticket,
        'assignedTechs' => $assignedTechs,
        'userProfile' => $userProfile,
        'userName' => $userName,
    ])
</div>
