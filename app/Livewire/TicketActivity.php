<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class TicketActivity extends Component
{
    public $activities = [];
    public $assignedTechs = [];

    #[On('ticket-updated')]
    public function refreshData($data)
    {
        $this->assignedTechs = $data['assignedTechs'];
        $this->activities = $data['activities'];
    }

    public function render()
    {
        return view('livewire.ticket-activity');
    }
}
