<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class TicketActivityForm extends Component
{
    public $ticket;
    public $assignedTechs = [];
    public $userProfile;
    public $userName;
    public $activeTab = 'followup';

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    #[On('ticket-updated')]
    public function refreshData($data)
    {
        $this->ticket = $data['ticket'];
        $this->assignedTechs = $data['assignedTechs'];
    }

    public function render()
    {
        return view('livewire.ticket-activity-form');
    }
}
