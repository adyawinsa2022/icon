<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class TicketController extends Controller
{
    protected $glpiApiUrl;
    protected $appToken;
    protected $apiHelper;

    public function __construct(ApiHelper $apiHelper)
    {
        $this->glpiApiUrl = config('glpi.api_url');
        $this->appToken = config('glpi.api_app_token');
        $this->apiHelper = $apiHelper;
    }

    /*************************************************
     * Function: index
     * Description: menampilkan daftar tabel tiket
     *************************************************/
    public function index()
    {
        $sessionToken = Session::get('glpi_session_token');
        $userId = Session::get('glpi_user_id');
        $userProfile = Session::get('glpi_user_profile');

        if (in_array($userProfile, ['Technician', 'Super-Admin'])) {
            // Jika login sebagai Tech/Admin
            $title = 'Tiket Belum Selesai';
            $params = [
                'criteria[0][field]' => 12,
                'criteria[0][searchtype]' => 'equals',
                'criteria[0][value]' => 'notold',
                'sort[0]' => 19,
                'order[0]' => 'DESC',
            ];
        } else {
            // Jika login sebagai User biasa
            $title = 'Tiket Saya';
            $params = [
                'criteria[0][field]' => 4,
                'criteria[0][searchtype]' => 'equals',
                'criteria[0][value]' => $userId,
            ];
        }

        // Build Parameter
        $query = http_build_query($params);
        $url = rtrim($this->glpiApiUrl, '/') . '/search/Ticket?' . $query;

        // Request ke Enpoint /search dengan Parameter sebelumnya
        $response = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->get($url);
        $data = $response->json();
        $ticketsRaw = $data['data'] ?? [];

        // Remap raw key dari GLPI ke readable key 
        $tickets = collect($ticketsRaw)->map(function ($ticket) {
            return [
                'id' => $ticket['2'] ?? null,
                'name' => $ticket['1'] ?? null,
                'requester_id' => $ticket['4'] ?? null,
                'status' => $this->apiHelper->getStatusName($ticket['12'] ?? 0),
                'date_mod' => $ticket['19'] ?? null,
            ];
        })->values();

        // Sort Last Update ke terbaru
        $tickets = $tickets->sortByDesc('date_mod')->values();

        return view('ticket.index', compact(
            'title',
            'tickets',
        ));
    }

    /*************************************************
     * Function: history
     * Description: menampilkan daftar tabel riwayat tiket perangkat
     *************************************************/
    public function history(Request $request, $deviceName)
    {
        // Ambil halaman saat ini (default 1)
        $page = $request->input('page', 1);
        $perPage = 15;
        $start = ($page - 1) * $perPage;
        $end = $start + $perPage - 1;

        $sessionToken = Session::get('glpi_session_token');
        $userId = Session::get('glpi_user_id');
        $userProfile = Session::get('glpi_user_profile');

        // Cari di nama GLPI langsung
        $foundDevice = $this->apiHelper->getIdByNameSearch(null, $deviceName);
        if (empty($foundDevice)) {
            abort(404);
        }

        // Jika ada perangkat, ambil Tiket bedasarkan tipe perangkat
        $device = $this->apiHelper->getResource($foundDevice['type'], $foundDevice['id'], $sessionToken);
        // dd($device['name']);

        $title = 'Tiket ' . $device['name'];

        $params = [
            'criteria[0][field]' => 131,
            'criteria[0][searchtype]' => 'equals',
            'criteria[0][value]' => $foundDevice['type'],
            'forcedisplay[0]' => 13,
            'forcedisplay[2]' => 4,
            'forcedisplay[3]' => 12,
            'forcedisplay[4]' => 19,
            'range' => "$start-$end",
        ];

        // Build Parameter
        $query = http_build_query($params);
        $url = rtrim($this->glpiApiUrl, '/') . '/search/Ticket?' . $query;

        // Request ke Enpoint /search dengan Parameter sebelumnya
        $response = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->get($url);
        $data = $response->json();
        $ticketsRaw = $data['data'] ?? [];
        $totalTickets = $data['totalcount'];
        $totalPages = ceil($totalTickets / $perPage);

        $deviceId = $foundDevice['id'];
        $ticketsRaw = array_filter($ticketsRaw, function ($item) use ($deviceId) {
            return isset($item[13]) && (int)$item[13] === (int)$deviceId;
        });

        // Remap raw key dari GLPI ke readable key 
        $tickets = collect($ticketsRaw)->map(function ($ticket) {
            return [
                'id' => $ticket['2'] ?? null,
                'name' => $ticket['1'] ?? null,
                'requester_id' => $ticket['4'] ?? null,
                'status' => $this->apiHelper->getStatusName($ticket['12'] ?? 0),
                'date_mod' => $ticket['19'] ?? null,
                // tambah key lainnya sesuai kebutuhan
            ];
        })->values();

        // Sort Last Update ke terbaru
        $tickets = $tickets->sortByDesc('date_mod')->values();


        return view('ticket.index', compact(
            'title',
            'tickets',
            'userProfile',
            'page',
            'totalPages',
        ));
    }

    /*************************************************
     * Function: create
     * Description: menampilkan form untuk membuat tiket baru
     *************************************************/
    public function create($deviceName = null)
    {
        $device = null;
        $sessionToken = Session::get('glpi_session_token');

        // Ambil perangkat jika ada
        if ($deviceName) {
            // Cari di GLPI langsung
            $foundDevice = $this->apiHelper->getIdByNameSearch(null, $deviceName);
            if (empty($foundDevice)) {
                abort(404);
            }
            $foundDeviceId = $foundDevice['id'];
            $foundDeviceType = $foundDevice['type'];

            $deviceResponse = Http::withHeaders([
                'App-Token' => $this->appToken,
                'Session-Token' => $sessionToken,
            ])->get($this->glpiApiUrl . "/$foundDeviceType/$foundDeviceId");

            $device = $deviceResponse->successful() ? $deviceResponse->json() : null;

            // Remap ITIL Category bedasarkan tipe Perangkat
            if ($foundDeviceType === 'Monitor') {
                $itilCategory = 'Hardware';
            } else {
                $itilCategory = $foundDeviceType;
            }

            $category = $this->apiHelper->getIdByNameSearch('ITILCategory', $itilCategory);
            $categories = $this->apiHelper->getResource('ITILCategory', $category['id'], $sessionToken);
            $locations = $this->apiHelper->getResource('Location', $device['locations_id'], $sessionToken);
        } else {
            // Ambil kategori untuk dropdown
            $categoriesResponse = Http::withHeaders([
                'App-Token' => $this->appToken,
                'Session-Token' => $sessionToken,
            ])->get($this->glpiApiUrl . '/ITILCategory');

            $categories = $categoriesResponse->successful() ? $categoriesResponse->json() : [];

            // Ambil lokasi untuk dropdown
            $locationsResponse = Http::withHeaders([
                'App-Token' => $this->appToken,
                'Session-Token' => $sessionToken,
            ])->get($this->glpiApiUrl . '/Location', [
                'range' => '0-100'
            ]);

            $locations = $locationsResponse->successful() ? $locationsResponse->json() : [];
        }

        return view(
            'ticket.create',
            compact(
                'device',
                'categories',
                'locations',
            )
        );
    }

    /*************************************************
     * Function: store
     * Description: menyimpan tiket baru dari Form
     *************************************************/
    public function store(Request $request)
    {
        $errorMessage = [
            'required' => 'Kolom :attribute harus diisi.',
            'max' => 'Kolom :attribute Maksimal :max karakter.',
        ];
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category_id'    => 'required|integer',
            'location_id'    => 'required|integer',
            'photo'       => 'nullable|string',
        ], $errorMessage);

        $file = null;
        if ($request->filled('photo')) {
            $file = $request->photo;
            // Cek apakah base64 diawali dengan prefix mime yang benar
            if (!preg_match('/^data:image\/(jpeg|jpg|png);base64,/', $file)) {
                return back()->withErrors(['compressed_photo' => 'Format foto harus JPG atau PNG.']);
            }
        }

        $sessionToken = Session::get('glpi_session_token');
        $userId = Session::get('glpi_user_id');

        $payload = [
            'input' => [
                'name' => $request->title,
                'content' => $request->description,
                'itilcategories_id' => $request->category_id,
                'locations_id' => $request->location_id,
                'users_id_recipient' => $userId
            ]
        ];

        // 1. Buat tiket baru
        $ticketResponse = Http::withHeaders([
            'App-Token'     => $this->appToken,
            'Session-Token' => $sessionToken,
            'Content-Type'  => 'application/json',
        ])->post(
            $this->glpiApiUrl . '/Ticket',
            $payload
        );

        if (!$ticketResponse->successful()) {
            return back()->withErrors(['msg' => 'Gagal membuat tiket']);
        }

        $ticketData = $ticketResponse->json();
        $ticketId = $ticketData['id'] ?? null;

        // 2.1. Upload file jika ada
        if ($file) {
            [$meta, $encodedData] = explode(',', $file);
            $decodedImage = base64_decode($encodedData);

            // Buat stream dari binary string
            $stream = \GuzzleHttp\Psr7\Utils::streamFor($decodedImage);

            // Nama file
            $filename = $request->photo_name;

            $manifest = json_encode([
                'input' => [
                    'name'      => 'Ticket Document ' . $ticketId,
                    '_filename' => ["file0"]
                ]
            ]);

            $client = new \GuzzleHttp\Client();

            $uploadResponse = $client->request('POST', $this->glpiApiUrl . '/Document', [
                'headers' => [
                    'App-Token'     => $this->appToken,
                    'Session-Token' => $sessionToken,
                ],
                'multipart' => [
                    [
                        'name'     => 'uploadManifest',
                        'contents' => $manifest,
                        'headers'  => ['Content-Type' => 'application/json']
                    ],
                    [
                        'name'     => 'filename[0]',
                        'contents' => $stream,
                        'filename' => $filename
                    ]
                ]
            ]);

            $uploadedDocument = json_decode($uploadResponse->getBody()->getContents(), true);
            $documentId = $uploadedDocument['id'];

            if ($documentId) {
                // Hubungkan ke tiket
                Http::withHeaders([
                    'App-Token'     => $this->appToken,
                    'Session-Token' => $sessionToken,
                ])->post($this->glpiApiUrl . '/Document_Item', [
                    'input' => [
                        'documents_id' => $documentId,
                        'itemtype'     => 'Ticket',
                        'items_id'     => $ticketId
                    ]
                ]);
            }
        }

        // 2.2 Jika ada perangkat, tambahkan ke tiket melalui /Item_Ticket
        if ($ticketId && $request->filled('device_id') && $request->filled('category_id')) {
            $ticketType = Http::withHeaders([
                'App-Token' => $this->appToken,
                'Session-Token' => $sessionToken,
            ])->get($this->glpiApiUrl . "/ITILCategory/$request->category_id")->json()['name'];

            Http::withHeaders([
                'App-Token' => $this->appToken,
                'Session-Token' => $sessionToken,
            ])->post($this->glpiApiUrl . '/Item_Ticket', [
                'input' => [
                    'items_id' => (int) $request->device_id,
                    'itemtype' => $ticketType, // Computer / Printer
                    'tickets_id' => $ticketId,
                ]
            ]);
        }

        // 3. Pastikan user login ditambahkan sebagai requester
        $userTicketResponse = Http::withHeaders([
            'App-Token'     => $this->appToken,
            'Session-Token' => $sessionToken,
            'Content-Type'  => 'application/json',
        ])->post($this->glpiApiUrl . "/Ticket_User", [
            'input' => [
                'tickets_id' => $ticketId,
                'users_id'   => $userId,
                'type'       => 1 // 1 = requester
            ]
        ]);

        return redirect()->route('ticket.index')->with('success', 'Tiket berhasil dibuat!');
    }

    /*************************************************
     * Function: show
     * Description: menampilkan detail tiket
     *************************************************/
    public function show($id)
    {
        $sessionToken = Session::get('glpi_session_token');
        $userId = Session::get('glpi_user_id');
        $userProfile = Session::get('glpi_user_profile');
        $userName = Session::get('glpi_user_name');

        // Ambil data tiket
        $response = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->get($this->glpiApiUrl . "/Ticket/$id");
        if (in_array($response->status(), [403, 404])) {
            abort($response->status());
        }
        $ticket = $response->json();

        // Ambil Kategori Tiket
        $ticket['categoryName'] = $this->apiHelper->getResource('ITILCategory', $ticket['itilcategories_id'] ?? null, $sessionToken)['name'] ?? '-';
        // Ambil Lokasi Tiket
        $ticket['locationName'] = $this->apiHelper->getResource('Location', $ticket['locations_id'] ?? null, $sessionToken)['name'] ?? '-';
        // Ambil Pembuat Tiket
        $ticket['requesterName'] = $this->apiHelper->getUserName($ticket['users_id_recipient'] ?? null, $sessionToken);
        // Ambil Status Tiket
        $ticket['statusName']    = $this->apiHelper->getStatusName($ticket['status'] ?? 0);

        if ($ticket['users_id_recipient'] == $userId) {
            $ticket['is_requester'] = true;
        } else {
            $ticket['is_requester'] = false;
        }

        // Kumpulkan User yang terkait dengan Tiket
        $ticketUsers = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->get($this->glpiApiUrl . "/Ticket/$id/Ticket_User")->json() ?? [];

        $assignedTechs = [];

        // Ambil hanya Teknisi
        foreach ($ticketUsers as $tu) {
            if (($tu['type'] ?? '') == 2) { // type=2 = assigned tech
                $assignedTechs[] = $this->apiHelper->getUserName($tu['users_id'] ?? null, $sessionToken);
            }
        }

        // Kumpulkan Item yang terkait dengan Tiket
        $ticketItem = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->get($this->glpiApiUrl . "/Ticket/$id/Item_Ticket")->json() ?? [];

        if (!empty($ticketItem)) {
            $ticket['itemName'] = $this->apiHelper->getResource($ticketItem[0]['itemtype'], $ticketItem[0]['items_id'] ?? null, $sessionToken)['name'];
        } else {
            $ticket['itemName'] = '-';
        }

        $activities = [];

        // 1. ITIL Followups
        $followups = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->get($this->glpiApiUrl . "/Ticket/$id/ITILFollowup", ['range' => '0-49'])->json() ?? [];

        foreach ($followups as $f) {
            if (empty(trim($f['content'] ?? ''))) continue;
            $activities[] = [
                'type' => 'followup',
                'date' => $f['date'] ?? '',
                'author_name' => $this->apiHelper->getUserName($f['users_id'] ?? null, $sessionToken),
                'content' => $f['content'] ?? '',
            ];
        }

        // 2. Tasks
        $tasks = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->get($this->glpiApiUrl . "/Ticket/$id/TicketTask")->json() ?? [];

        foreach ($tasks as $t) {
            if (empty(trim($t['content'] ?? ''))) continue;
            $activities[] = [
                'type' => 'task',
                'date' => $t['date'] ?? '',
                'author_name' => $this->apiHelper->getUserName($t['users_id'] ?? null, $sessionToken),
                'content' => $t['content'] ?? '',
                'begin' => $t['begin'] ?? '',
                'end' => $t['end'] ?? '',
            ];
        }

        // 3. Solutions
        $solutions = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->get($this->glpiApiUrl . "/Ticket/$id/ITILSolution")->json() ?? [];

        foreach ($solutions as $s) {
            if (empty(trim($s['content'] ?? ''))) continue;
            $ticket['lastSolutionId'] = $s['id'];
            $activities[] = [
                'type' => 'solution',
                'date' => $s['date_creation'] ?? '',
                'author_name' => $this->apiHelper->getUserName($s['users_id'] ?? null, $sessionToken),
                'content' => $s['content'] ?? '',
            ];
        }

        // 4️. Documents
        $documents = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->get($this->glpiApiUrl . "/Ticket/$id/Document_Item")->json() ?? [];

        if (is_array($documents) && count($documents) > 0) {
            foreach ($documents as $d) {
                $docDetail = $this->apiHelper->getResource('Document', $d['documents_id'], $sessionToken);
                $fileName  = $docDetail['name'] ?? 'Unknown file';
                $url = route('document.show', $d['documents_id']);
                $activities[] = [
                    'type' => 'document',
                    'date' => $d['date_creation'] ?? '',
                    'author_name' => $this->apiHelper->getUserName($d['users_id'] ?? null, $sessionToken),
                    'content' => "<a href='$url' target='_blank'>$fileName</a>"
                ];
            }
        }

        // Urutkan berdasarkan tanggal
        usort($activities, fn($a, $b) => strtotime($a['date']) <=> strtotime($b['date']));

        return view('ticket.show', compact(
            'ticket',
            'activities',
            'assignedTechs',
            'userProfile',
            'userName',
        ));
    }

    /*************************************************
     * Function: take
     * Description: Tech mengambil tiket baru
     *************************************************/
    public function take($ticketId)
    {
        $sessionToken = Session::get('glpi_session_token');
        $userId = Session::get('glpi_user_id');

        $res = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->post($this->glpiApiUrl . '/Ticket_User', [
            'input' => [
                'tickets_id' => $ticketId,
                'users_id' => $userId,
                'type' => 2 // Technician
            ]
        ]);


        // Request ke GLPI, Update Status Tiket
        $resStatus = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->put(
            $this->glpiApiUrl . "/Ticket/$ticketId",
            ['input' => [
                'status' => 2,
            ]]
        );

        if ($res->successful() && $resStatus->successful()) {
            return redirect()->route('ticket.show', $ticketId)->with('success', 'Berhasil mengambil Tiket.');
        }
        return back()->with('error', 'Gagal menambahkan followup.');
    }

    /*************************************************
     * Function: followup
     * Description: menambahkan followup ke tiket
     *************************************************/
    public function followup(Request $request, $id)
    {
        $sessionToken = Session::get('glpi_session_token');
        $userId = Session::get('glpi_user_id');

        $errorMessage = [
            'required' => 'Kolom :attribute harus diisi.',
            'max' => 'Maksimal :max karakter.',
        ];

        $request->validate([
            'content' => 'required|string|max:2000',
        ], $errorMessage);

        $payload = [
            'input' => [
                'items_id' => $id,
                'itemtype' => 'Ticket',
                'content' => $request->content,
                'users_id' => $userId
            ]
        ];

        $res = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->post($this->glpiApiUrl . '/ITILFollowup', $payload);

        if ($res->successful()) {
            return redirect()->route('ticket.show', $id)->with('success', 'Followup berhasil ditambahkan.');
        }

        return back()->with('error', 'Gagal menambahkan followup.');
    }
    /*************************************************
     * Function: task
     * Description: menambahkan task ke tiket
     *************************************************/
    public function task(Request $request, $ticketId)
    {
        $sessionToken = Session::get('glpi_session_token');
        $userId = Session::get('glpi_user_id');
        $begin = null;
        $actiontime = null;
        $end = null;

        $errorMessage = [
            'required' => 'Kolom :attribute harus diisi.',
            'max' => 'Maksimal :max karakter.',
        ];

        $request->validate([
            'content' => 'required|string|max:2000',
        ], $errorMessage);

        // Jika ada jadwal
        $isPlanned = $request->planCheckbox ?? null;
        if ($isPlanned) {
            // Hitung end dari begin dan actiontime
            if (!$request->filled('begin')) {
                return back()->withErrors(['msg' => 'Jadwal tidak lengkap.']);
            }
            $begin = Carbon::parse($request->begin);
            $actiontime = ($request->duration_hours * 3600) + ($request->duration_minutes * 60);
            if ($actiontime < 1) {
                return back()->withErrors(['msg' => 'Jadwal tidak lengkap.']);
            }
            $end = $begin->copy()->addSeconds($actiontime)->format('Y-m-d H:i:s');
            $begin = $begin->format('Y-m-d H:i:s');
        }

        $payload = [
            'input' => [
                'tickets_id'    => $ticketId,
                'content'       => $request->content,
                'users_id'      => $userId,
                'users_id_tech' => $userId,
            ]
        ];

        // Kalau ada jadwal, tambahkan key ke payload
        if ($actiontime && $begin && $end) {
            $payload['input']['actiontime'] = $actiontime;
            $payload['input']['begin'] = $begin;
            $payload['input']['end'] = $end;
        }

        $res = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->post($this->glpiApiUrl . '/TicketTask', $payload);

        // Jika task tidak ada jadwal, cek status tiket
        if (!$actiontime && !$begin && !$end) {
            // Update Status Tiket ke Proses
            $payloadStatus = [
                'input' => [
                    'status' => 2,
                ]
            ];

            // Request ke GLPI, Update Status Tiket
            $resStatus = Http::withHeaders([
                'App-Token' => $this->appToken,
                'Session-Token' => $sessionToken,
            ])->put($this->glpiApiUrl . "/Ticket/$ticketId", $payloadStatus);
        }

        if ($res->successful()) {
            return redirect()->route('ticket.show', $ticketId)->with('success', 'Task berhasil ditambahkan.');
        }

        return back()->with('error', 'Gagal menambahkan followup.');
    }

    /*************************************************
     * Function: solution
     * Description: menambahkan solution ke tiket
     *************************************************/
    public function solution(Request $request, $ticketId)
    {
        $sessionToken = Session::get('glpi_session_token');
        $userId = Session::get('glpi_user_id');

        $errorMessage = [
            'required' => 'Kolom :attribute harus diisi.',
            'max' => 'Maksimal :max karakter.',
        ];

        $request->validate([
            'content' => 'required|string|max:2000',
        ], $errorMessage);

        $payload = [
            'input' => [
                'items_id' => $ticketId,
                'itemtype' => 'Ticket',
                'content' => $request->content,
                'users_id' => $userId
            ]
        ];

        $res = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->post($this->glpiApiUrl . '/ITILSolution', $payload);

        if ($res->successful()) {
            return redirect()->route('ticket.show', $ticketId)->with('success', 'Solution berhasil ditambahkan.');
        }

        return back()->with('error', 'Gagal menambahkan followup.');
    }

    /*************************************************
     * Function: approval
     * Description: menambahkan followup ke tiket
     *************************************************/
    public function approval(Request $request, $ticketId, $solutionId = null)
    {
        $sessionToken = Session::get('glpi_session_token');
        $userId = Session::get('glpi_user_id');

        $errorMessage = [
            'max' => 'Maksimal :max karakter.',
        ];

        $request->validate([
            'content' => 'nullable|string|max:2000',
        ], $errorMessage);

        // Cek tombol yang ditekan
        if ($request->answer == 'approve') {
            $status = 6; // Tutup
            if ($request->filled('content')) {
                $contentPayload = $request->content;
            } else {
                $contentPayload = "Tiket telah ditutup atas persetujuan User";
            }
        } else if ($request->answer == 'refuse') {
            $status = 2; // Kembali ke Proses
            if ($request->filled('content')) {
                $contentPayload = $request->content;
            } else {
                $contentPayload = "Tiket gagal ditutup atas penolakan User";
            }
        }

        // Set Payload insert Followup
        $payloadFollowup = [
            'input' => [
                'items_id' => $ticketId,
                'itemtype' => 'Ticket',
                'content' => $contentPayload,
                'users_id' => $userId,
            ]
        ];

        // Request ke GLPI, Insert Followup
        $resFollowup = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->post($this->glpiApiUrl . '/ITILFollowup', $payloadFollowup);

        // Jika ada Solusi di tiket, update status Solusi
        if ($solutionId) {
            $solutionStatus = $request->action == 'approve' ? 4 : 3; // 4 = approved, 3 = refused

            $payloadSolution = [
                'input' => [
                    'status' => $solutionStatus,
                ]
            ];

            $resUpdateSolution = Http::withHeaders([
                'App-Token' => $this->appToken,
                'Session-Token' => $sessionToken,
            ])->put($this->glpiApiUrl . "/ITILSolution/$solutionId", $payloadSolution);
        }

        // Set Payload update Status Tiket
        $payloadStatus = [
            'input' => [
                'status' => $status,
            ]
        ];

        // Request ke GLPI, Update Status Tiket
        $resStatus = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->put($this->glpiApiUrl . "/Ticket/$ticketId", $payloadStatus);

        if ($resStatus->successful() && $resFollowup->successful() && (!isset($resUpdateSolution) || $resUpdateSolution->successful())) {
            return redirect()->route('ticket.show', $ticketId)->with('success', 'Approval berhasil ditambahkan.');
        }

        return back()->with('error', 'Gagal menambahkan Approval.');
    }
}
