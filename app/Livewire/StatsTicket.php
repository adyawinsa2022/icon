<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class StatsTicket extends Component
{
    public $glpiApiUrl;
    public $appToken;
    public $ticket = [];

    public function mount()
    {
        // Inisialisasi properti dari config
        $this->glpiApiUrl = config('glpi.api_url');
        $this->appToken = config('glpi.api_app_token');
        $this->getStatsTicket();
    }

    public function getStatsTicket()
    {
        $sessionToken = Session::get('glpi_session_token');
        $userId = Session::get('glpi_user_id');
        $userProfile = Session::get('glpi_user_profile');

        $statusGroups = [
            'total'  => ['all'],        // Semua tiket
            'active' => ['notold'],    // Open + In Progress
            'solved' => [5],       // Solved
            'closed' => [6],       // Closed
        ];

        // Loop untuk masing-masing group
        foreach ($statusGroups as $key => $statuses) {

            // Filter utama berdasarkan role
            if (in_array($userProfile, ['Technician', 'Super-Admin'])) {
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

            // Simpan hasil totalcount ke dalam array
            $this->ticket[$key] = $data['totalcount'] ?? 0;
        }
    }

    public function render()
    {
        // dd($this->ticket);
        return view('livewire.stats-ticket');
    }
}
