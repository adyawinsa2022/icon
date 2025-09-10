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
        // Controller hanya mengembalikan view, Livewire yang memproses datanya
        return view('ticket.index');
    }

    /*************************************************
     * Function: history
     * Description: menampilkan daftar tabel riwayat tiket perangkat
     *************************************************/
    public function history(Request $request, $deviceName)
    {
        // Controller hanya mengembalikan view dengan parameter, Livewire yang memproses datanya
        return view('ticket.index', compact(
            'deviceName',
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
        return view('ticket.show', compact(
            'id',
        ));
    }
}
