<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class TicketShow extends Component
{
    public $ticketId;
    public $ticket = [];
    public $assignedTechs = [];
    public $activities = [];
    public $userId;
    public $userProfile;
    public $userName;

    public $sessionToken;

    public function mount($ticketId)
    {
        $this->ticketId = $ticketId;
        $this->userId = Session::get('glpi_user_id');
        $this->userProfile = Session::get('glpi_user_profile');
        $this->userName = Session::get('glpi_user_name');

        $this->sessionToken = Session::get('glpi_session_token');

        $this->loadTicketDetail();
        $this->loadTicketActivities();
    }

    #[On('update-ticket')]
    public function refreshTicket()
    {
        $this->loadTicketDetail();
        $this->loadTicketActivities();
        $this->dispatch('ticket-updated', data: [
            'ticket' => $this->ticket,
            'assignedTechs' => $this->assignedTechs,
            'activities' => $this->activities
        ]);
    }

    public function loadTicketDetail()
    {
        // === Ambil data tiket utama ===
        $response = Http::withHeaders([
            'App-Token' => config('glpi.api_app_token'),
            'Session-Token' => $this->sessionToken,
        ])->get(config('glpi.api_url') . "/Ticket/{$this->ticketId}");

        if (in_array($response->status(), [403, 404])) {
            abort($response->status());
        }

        $ticket = $response->json();

        $apiHelper = new \App\Helpers\ApiHelper();

        // Tambahan detail
        $ticket['categoryName'] = $apiHelper->getResource('ITILCategory', $ticket['itilcategories_id'] ?? null, $this->sessionToken)['name'] ?? '-';
        $ticket['locationName'] = $apiHelper->getResource('Location', $ticket['locations_id'] ?? null, $this->sessionToken)['name'] ?? '-';
        $ticket['requesterName'] = $apiHelper->getUserName($ticket['users_id_recipient'] ?? null, $this->sessionToken);
        $ticket['statusName'] = $apiHelper->getStatusName($ticket['status'] ?? 0);
        $ticket['is_requester'] = ($ticket['users_id_recipient'] == $this->userId);

        // === Ambil teknisi yang ditugaskan ===
        $ticketUsers = Http::withHeaders([
            'App-Token' => config('glpi.api_app_token'),
            'Session-Token' => $this->sessionToken,
        ])->get(config('glpi.api_url') . "/Ticket/{$this->ticketId}/Ticket_User")->json() ?? [];

        $this->assignedTechs = [];
        foreach ($ticketUsers as $tu) {
            if (($tu['type'] ?? '') == 2) { // type=2 = assigned tech
                $this->assignedTechs[] = $apiHelper->getUserName($tu['users_id'] ?? null, $this->sessionToken);
            }
        }

        // === Ambil item terkait tiket ===
        $ticketItem = Http::withHeaders([
            'App-Token' => config('glpi.api_app_token'),
            'Session-Token' => $this->sessionToken,
        ])->get(config('glpi.api_url') . "/Ticket/{$this->ticketId}/Item_Ticket")->json() ?? [];

        $ticket['itemName'] = !empty($ticketItem)
            ? $apiHelper->getResource($ticketItem[0]['itemtype'], $ticketItem[0]['items_id'] ?? null, $this->sessionToken)['name']
            : '-';

        $this->ticket = $ticket;

        // === Buat "instance baru" supaya child menerima prop terbaru ===
        $this->assignedTechs = array_values($this->assignedTechs);
        $this->ticket = json_decode(json_encode($this->ticket), true);
    }

    public function loadTicketActivities()
    {
        $apiHelper = new \App\Helpers\ApiHelper();
        $activities = [];

        // 1. ITIL Followups
        $followups = Http::withHeaders([
            'App-Token' => config('glpi.api_app_token'),
            'Session-Token' => $this->sessionToken,
        ])->get(config('glpi.api_url') . "/Ticket/{$this->ticketId}/ITILFollowup", ['range' => '0-49'])->json() ?? [];

        foreach ($followups as $f) {
            if (empty(trim($f['content'] ?? ''))) continue;
            $activities[] = [
                'type' => 'followup',
                'date' => $f['date'] ?? '',
                'author_name' => $apiHelper->getUserName($f['users_id'] ?? null, $this->sessionToken),
                'content' => $f['content'] ?? '',
            ];
        }

        // 2. Tasks
        $tasks = Http::withHeaders([
            'App-Token' => config('glpi.api_app_token'),
            'Session-Token' => $this->sessionToken,
        ])->get(config('glpi.api_url') . "/Ticket/{$this->ticketId}/TicketTask")->json() ?? [];

        foreach ($tasks as $t) {
            if (empty(trim($t['content'] ?? ''))) continue;
            $activities[] = [
                'type' => 'task',
                'date' => $t['date'] ?? '',
                'author_name' => $apiHelper->getUserName($t['users_id'] ?? null, $this->sessionToken),
                'content' => $t['content'] ?? '',
                'begin' => $t['begin'] ?? '',
                'end' => $t['end'] ?? '',
            ];
        }

        // 3. Solutions
        $solutions = Http::withHeaders([
            'App-Token' => config('glpi.api_app_token'),
            'Session-Token' => $this->sessionToken,
        ])->get(config('glpi.api_url') . "/Ticket/{$this->ticketId}/ITILSolution")->json() ?? [];

        foreach ($solutions as $s) {
            if (empty(trim($s['content'] ?? ''))) continue;
            $this->ticket['lastSolutionId'] = $s['id'];
            $activities[] = [
                'type' => 'solution',
                'date' => $s['date_creation'] ?? '',
                'author_name' => $apiHelper->getUserName($s['users_id'] ?? null, $this->sessionToken),
                'content' => $s['content'] ?? '',
            ];
        }

        // 4️. Documents
        $documents = Http::withHeaders([
            'App-Token' => config('glpi.api_app_token'),
            'Session-Token' => $this->sessionToken,
        ])->get(config('glpi.api_url') . "/Ticket/{$this->ticketId}/Document_Item")->json() ?? [];

        if (is_array($documents) && count($documents) > 0) {
            foreach ($documents as $d) {
                $docDetail = $apiHelper->getResource('Document', $d['documents_id'], $this->sessionToken);
                $fileName  = $docDetail['name'] ?? 'Unknown file';
                $url = route('document.show', $d['documents_id']);
                $activities[] = [
                    'type' => 'document',
                    'date' => $d['date_creation'] ?? '',
                    'author_name' => $apiHelper->getUserName($d['users_id'] ?? null, $this->sessionToken),
                    'content' => "<a href='$url' target='_blank'>$fileName</a>"
                ];
            }
        }

        // Urutkan berdasarkan tanggal
        usort($activities, fn($a, $b) => strtotime($a['date']) <=> strtotime($b['date']));
        $this->activities = $activities;
    }

    public function render()
    {
        return view('livewire.ticket-show');
    }
}
