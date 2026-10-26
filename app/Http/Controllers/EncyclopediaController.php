<?php

namespace App\Http\Controllers;

use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class EncyclopediaController extends Controller
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

    public function index()
    {
        return view('encyclopedia.index');
    }

    public function show($id)
    {
        $sessionToken = Session::get('glpi_session_token');

        // Jika belum login, initSession pakai akun API khusus
        if (!$sessionToken) {
            $tempSession = Http::withHeaders([
                'App-Token' => $this->appToken,
                'Content-Type' => 'application/json',
            ])->post($this->glpiApiUrl . '/initSession', [
                'login' => config('glpi.api_user'),
                'password' => config('glpi.api_password'),
            ])->json()['session_token'];
            $sessionToken = $tempSession;
        }

        $response = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->get($this->glpiApiUrl . '/KnowbaseItem/' . $id);

        $article = $response->json();

        $payloadStatus = [
            'input' => [
                'view' => $article['view'] + 1,
            ]
        ];

        // Request ke GLPI, Update Status Tiket
        Http::withHeaders([
            'App-Token' => config('glpi.api_app_token'),
            'Session-Token' => $sessionToken,
        ])->put(config('glpi.api_url') . "/KnowbaseItem/{$id}", $payloadStatus);

        // Hapus session jika tadi login dengan akun khusus
        if ($tempSession) {
            // Hapus session token sementara
            Http::withHeaders([
                'App-Token' => $this->appToken,
                'Session-Token' => $tempSession,
            ])->get($this->glpiApiUrl . '/killSession');
        }

        return view('encyclopedia.article', compact('article'));
    }
}
