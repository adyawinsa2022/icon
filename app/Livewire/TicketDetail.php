<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class TicketDetail extends Component
{
    public $ticket;
    public $assignedTechs = [];
    public $userProfile;
    public $userName;

    #[On('ticket-updated')]
    public function refreshData($data)
    {
        $this->ticket = $data['ticket'];
        $this->assignedTechs = $data['assignedTechs'];
    }

    public function takeTicket()
    {
        $sessionToken = Session::get('glpi_session_token');
        $userId = Session::get('glpi_user_id');

        $res = Http::withHeaders([
            'App-Token' => config('glpi.api_app_token'),
            'Session-Token' => $sessionToken,
        ])->post(config('glpi.api_url') . '/Ticket_User', [
            'input' => [
                'tickets_id' => $this->ticket['id'],
                'users_id' => $userId,
                'type' => 2 // Technician
            ]
        ]);

        if ($res->successful()) {
            $this->dispatch('show-toast', message: 'Berhasil mengambil tiket.', type: 'success');
            $this->dispatch('update-ticket')->to('ticket-show');
        } else {
            $this->dispatch('show-toast', message: 'Gagal mengambil tiket.', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.ticket-detail');
    }
}
