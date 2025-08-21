<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class DocumentController extends Controller
{
    protected $glpiApiUrl;
    protected $appToken;
    /*************************************************
     * Function: getResource (Helper)
     * Description: mengambil resource dari GLPI API
     *************************************************/
    private function getResource($endpoint, $id, $sessionToken)
    {
        if (!$id) return '-';
        $res = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->get($this->glpiApiUrl . "/$endpoint/$id");

        return $res->successful() ? ($res->json()) : '-';
    }

    public function __construct()
    {
        $this->glpiApiUrl = config('glpi.api_url');
        $this->appToken = config('glpi.api_app_token');
    }

    public function show($id)
    {
        $sessionToken = Session::get('glpi_session_token');
        $response = Http::withHeaders([
            'App-Token'     => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->get($this->glpiApiUrl . "/Document/$id?alt=media");

        return response($response->body(), 200)
            ->header('Content-Type', $response->header('Content-Type'));
    }
}
