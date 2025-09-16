<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AttentionTicket extends Component
{
    public $glpiApiUrl;
    public $appToken;
    public $userProfile;
    public $tickets = [];

    public function mount()
    {
        // Inisialisasi properti dari config
        $this->glpiApiUrl = config('glpi.api_url');
        $this->appToken = config('glpi.api_app_token');
        $this->userProfile = Session::get('glpi_user_profile');
        $this->getAttentionTicket();
    }

    public function getAttentionTicket()
    {
        $sessionToken = Session::get('glpi_session_token');
        $userId = Session::get('glpi_user_id');

        $statusGroups = [
            'active' => ['notold'],    // Open + In Progress
            'solved' => [5],       // Solved
        ];

        // Loop untuk masing-masing group
        foreach ($statusGroups as $key => $statuses) {

            // Filter utama berdasarkan role
            if (in_array($this->userProfile, ['Technician', 'Super-Admin'])) {
                // Bisa melihat semua tiket
                $params = [
                    'criteria[0][field]'      => 12,
                    'criteria[0][searchtype]' => 'equals',
                    'criteria[0][value]'      => 'all',
                ];
            } else {
                // Hanya tiket milik user yang login
                $params = [
                    'criteria[0][field]'      => 4,        // 4 = requester
                    'criteria[0][searchtype]' => 'equals',
                    'criteria[0][value]'      => $userId,
                ];
            }

            // Jika group memiliki filter status, tambahkan ke params
            if (!empty($statuses)) {
                foreach ($statuses as $index => $status) {
                    $statusIndex = count($params); // mulai dari index berikutnya

                    $params["criteria[$statusIndex][field]"] = 12;           // 12 = field status
                    $params["criteria[$statusIndex][searchtype]"] = 'equals';
                    $params["criteria[$statusIndex][value]"] = $status;
                }
            }

            // Build Parameter jadi query string
            $query = http_build_query($params);
            $url   = rtrim($this->glpiApiUrl, '/') . '/search/Ticket?' . $query;

            // Request ke Endpoint /search
            $response = Http::withHeaders([
                'App-Token'     => $this->appToken,
                'Session-Token' => $sessionToken,
            ])->get($url);

            $data = $response->json();

            if (!isset($data['data'])) {
                $this->tickets[$key] = 0;
            } else {
                $tickets = collect($data['data'])->map(function ($ticket) {
                    return [
                        'id' => $ticket['2'] ?? null,
                        'name' => $ticket['1'] ?? null,
                        'date' => $ticket['15'] ?? null,
                    ];
                })->values();

                // Simpan hasil totalcount ke dalam array
                $this->tickets[$key] = $tickets ?? 0;
            }
        }
    }

    public function render()
    {
        return view('livewire.attention-ticket');
    }
}
