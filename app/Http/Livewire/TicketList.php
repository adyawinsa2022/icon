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
    // use WithPagination;

    // Properti untuk state filter dan halaman
    public $glpiApiUrl;
    public $appToken;
    public $status = 'notold';
    public $page = 1;

    // Properti khusus untuk Livewire v2 agar pagination tidak reload
    protected $queryString = ['status'];

    public function gotoPage($page)
    {
        $this->page = $page;
    }

    public function previousPage()
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function nextPage()
    {
        $this->page++;
    }

    public function mount()
    {
        // Inisialisasi properti dari config
        $this->glpiApiUrl = config('glpi.api_url');
        $this->appToken = config('glpi.api_app_token');

        // Mengatur status awal dari URL
        if (request()->has('status')) {
            $this->status = request()->query('status');
        }
    }

    // Metode ini akan dipanggil Livewire saat properti status berubah
    public function updatedStatus()
    {
        $this->page = 1;
    }

    public function render(ApiHelper $apiHelper)
    {
        $perPage = 15; // Default data Ticket yang diambil dari GLPI
        $sessionToken = Session::get('glpi_session_token');
        $userId = Session::get('glpi_user_id');
        $userProfile = Session::get('glpi_user_profile');
        $page = $this->page; // Livewire secara otomatis menyediakan properti $this->page

        // Logic yang sama persis dengan yang ada di controller
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
        $totalPages = ceil($totalTickets / $perPage);

        // Remap raw key dari GLPI ke readable key
        $tickets = collect($ticketsRaw)->map(function ($ticket) use ($apiHelper) {
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
        $paginatedTickets = new LengthAwarePaginator(
            $tickets,
            $totalTickets,
            $perPage,
            $page,
            ['path' => Request::url()]
        );

        return view('livewire.ticket-list', [
            'title' => $title,
            'tickets' => $paginatedTickets,
            'userProfile' => $userProfile,
            'totalPages' => $totalPages,
            'status' => $this->status, // Mengirim status ke view
        ]);
    }
}
