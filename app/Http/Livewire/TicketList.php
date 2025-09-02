<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Helpers\ApiHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\LengthAwarePaginator;

class TicketList extends Component
{
    // Properti untuk state filter dan halaman
    public $glpiApiUrl;
    public $appToken;
    public $status = 'notold';
    public $page = 1;
    public $deviceName = null;

    protected $listeners = ['showDeviceHistory' => 'showDeviceHistory'];

    public function gotoPage($page)
    {
        $this->page = $page;
    }

    public function nextPage()
    {
        $this->page++;
    }

    public function previousPage()
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    // Menampilkan riwayat perangkat dari Scan QR
    public function showDeviceHistory($deviceName)
    {
        $this->deviceName = $deviceName;
        $this->page = 1;
    }

    // Metode ini akan dipanggil Livewire saat properti status berubah
    public function updatedStatus()
    {
        $this->deviceName = null;
        $this->page = 1;
    }

    public function mount($deviceName = null)
    {
        // Inisialisasi properti dari config
        $this->glpiApiUrl = config('glpi.api_url');
        $this->appToken = config('glpi.api_app_token');

        if ($deviceName) {
            $this->deviceName = $deviceName;
        }
    }

    public function render(ApiHelper $apiHelper)
    {
        $perPage = 15; // Default data Ticket yang diambil dari GLPI
        $sessionToken = Session::get('glpi_session_token');
        $userId = Session::get('glpi_user_id');
        $userProfile = Session::get('glpi_user_profile');
        $page = $this->page; // Livewire secara otomatis menyediakan properti $this->page

        $this->deviceName = strtoupper($this->deviceName);
        if ($this->deviceName) {
            // Jika ada perangkat, mengambil data tiket perangkat tersebut
            $foundDevice = $apiHelper->getIdByNameSearch(null, $this->deviceName);
            if (empty($foundDevice)) {
                abort(404);
            }
            $deviceId = $foundDevice['id'];
            $deviceType = $foundDevice['type'];
            $title = 'Tiket ' . $this->deviceName;
            $params = [
                'criteria[0][field]' => 131,
                'criteria[0][searchtype]' => 'equals',
                'criteria[0][value]' => $deviceType,
                'forcedisplay[0]' => 13,
                'forcedisplay[2]' => 4,
                'forcedisplay[3]' => 12,
                'forcedisplay[4]' => 19,
            ];

            $query = http_build_query($params);
            $url = rtrim($this->glpiApiUrl, '/') . '/search/Ticket?' . $query;

            $response = Http::withHeaders([
                'App-Token' => $this->appToken,
                'Session-Token' => $sessionToken,
            ])->get($url);

            $data = $response->json();
            $ticketsRaw = $data['data'] ?? [];

            // Filter tiket di sisi PHP berdasarkan ID perangkat
            $filteredTicketsRaw = collect($ticketsRaw)->filter(function ($item) use ($deviceId) {
                return isset($item[13]) && (int)$item[13] === (int)$deviceId;
            });

            $totalTickets = $filteredTicketsRaw->count();
            $tickets = $filteredTicketsRaw;
        } else {
            // Jika tidak ada perangkat, ambil semua tiket
            if (in_array($userProfile, ['Technician', 'Super-Admin'])) {
                $title = ($this->status == 'notold') ? 'Tiket Belum Selesai' : 'Semua Tiket';
                $params = [
                    'criteria[0][field]' => 12,
                    'criteria[0][searchtype]' => 'equals',
                    'criteria[0][value]' => "$this->status",
                    'sort[0]' => 19,
                    'order[0]' => 'DESC',
                ];
            } else {
                $title = 'Tiket Saya';
                $params = [
                    'criteria[0][field]' => 4,
                    'criteria[0][searchtype]' => 'equals',
                    'criteria[0][value]' => $userId,
                ];
            }

            // Tambahkan parameter pagination ke dalam params
            $params['range'] = (($page - 1) * $perPage) . '-' . (($page * $perPage) - 1);

            // Build Parameter
            $query = http_build_query($params);
            $url = rtrim($this->glpiApiUrl, '/') . '/search/Ticket?' . $query;

            // Request ke Endpoint /search
            $response = Http::withHeaders([
                'App-Token' => $this->appToken,
                'Session-Token' => $sessionToken,
            ])->get($url);

            $data = $response->json();
            $ticketsRaw = $data['data'] ?? [];
            $totalTickets = $data['totalcount'] ?? 0;
            $tickets = collect($ticketsRaw);
        }

        $totalPages = ceil($totalTickets / $perPage);

        // Remap raw key dari GLPI ke readable key
        $tickets = $tickets->map(function ($ticket) use ($apiHelper) {
            return [
                'id' => $ticket['2'] ?? null,
                'name' => $ticket['1'] ?? null,
                'requester_id' => $ticket['4'] ?? null,
                'status' => $apiHelper->getStatusName($ticket['12'] ?? 0),
                'date_mod' => $ticket['19'] ?? null,
            ];
        })->values();

        // Sort Last Update ke terbaru
        $tickets = $tickets->sortByDesc('date_mod')->values();

        // Buat objek Paginator secara manual
        // Jika data dari API sudah paginated, kita tidak perlu forPage lagi
        if (!$this->deviceName) {
            $paginatedTickets = new LengthAwarePaginator(
                $tickets, // Gunakan collection yang sudah di-fetch
                $totalTickets,
                $perPage,
                $page,
                ['path' => Request::url()]
            );
        } else {
            // Untuk mode riwayat perangkat, kita harus tetap menggunakan forPage
            $paginatedTickets = new LengthAwarePaginator(
                $tickets->forPage($page, $perPage),
                $totalTickets,
                $perPage,
                $page,
                ['path' => Request::url()]
            );
        }

        return view('livewire.ticket-list', [
            'title' => $title,
            'tickets' => $paginatedTickets,
            'userProfile' => $userProfile,
            'totalPages' => $totalPages,
            'status' => $this->status, // Mengirim status ke view
        ]);
    }
}
