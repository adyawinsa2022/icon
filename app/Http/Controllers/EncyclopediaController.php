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

        return view('encyclopedia.article', compact('article'));
    }
}
