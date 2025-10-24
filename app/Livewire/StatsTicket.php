<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class StatsTicket extends Component
{
    public $glpiApiUrl;
    public $appToken;
    public $ticket = [];
    public $range = 'month';
    public $value = null;

    public function mount()
    {
        // Inisialisasi properti dari config
        $this->glpiApiUrl = config('glpi.api_url');
        $this->appToken = config('glpi.api_app_token');
        $this->value = now()->format('Y-m');
        $this->getStatsTicket();
    }

    public function updatedRange($value)
    {

        switch ($this->range) {
            case 'day':
                $this->value = now()->toDateString(); // format: Y-m-d
                break;
            case 'week':
                $this->value = now()->format('o-\WW'); // format: Y-Www
                break;
            case 'month':
                $this->value = now()->format('Y-m'); // format: Y-m
                break;
        }

        $this->getStatsTicket();
    }

    public function updatedValue($value)
    {
        $this->getStatsTicket();
    }

    public function getStatsTicket()
    {
        $sessionToken = Session::get('glpi_session_token');
        $userId = Session::get('glpi_user_id');
        $userProfile = Session::get('glpi_user_profile');

        switch ($this->range) {
            case 'day':
                $startDate = Carbon::parse($this->value)->startOfDay();
                $endDate   = Carbon::parse($this->value)->endOfDay();
                break;

            case 'week':
                [$year, $week] = explode('-W', $this->value);
                $startDate = Carbon::now()->setISODate($year, $week)->startOfWeek();
                $endDate   = Carbon::now()->setISODate($year, $week)->endOfWeek();
                break;

            case 'month':
                $startDate = Carbon::parse($this->value)->startOfMonth();
                $endDate   = Carbon::parse($this->value)->endOfMonth();
                break;

            default:
                $startDate = null;
                $endDate = null;
                break;
        }

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

            if (!empty($startDate) && !empty($endDate)) {
                $startDate = Carbon::parse($startDate)->startOfDay()->format('Y-m-d H:i:s');
                $endDate   = Carbon::parse($endDate)->endOfDay()->format('Y-m-d H:i:s');

                $rangeIndex = count($params);
                $params["criteria[$rangeIndex][field]"] = 15;
                $params["criteria[$rangeIndex][searchtype]"] = 'morethan';
                $params["criteria[$rangeIndex][value]"] = $startDate;

                $dateEndIndex = $rangeIndex + 1;
                $params["criteria[$dateEndIndex][link]"] = 'AND';
                $params["criteria[$dateEndIndex][field]"] = 15;
                $params["criteria[$dateEndIndex][searchtype]"] = 'lessthan';
                $params["criteria[$dateEndIndex][value]"] = $endDate;
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
        return view('livewire.stats-ticket');
    }

    public function rendered()
    {
        $this->dispatch('rangeChanged', value: $this->range);
        $this->dispatch('ticketUpdated', ticket: $this->ticket);
    }
}
