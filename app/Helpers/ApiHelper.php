<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ApiHelper
{
    protected $glpiApiUrl;
    protected $appToken;

    public function __construct()
    {
        $this->glpiApiUrl = config('glpi.api_url');
        $this->appToken = config('glpi.api_app_token');
    }

    /*************************************************
     * Function: getUserName
     * Description: mencari nama user berdasarkan ID
     *************************************************/
    public function getUserName($id, $sessionToken)
    {
        if (!$id) return '-';

        $res = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken
        ])->get($this->glpiApiUrl . "/User/$id");

        $name = $res->successful()
            ? (($res->json()['firstname'] ?? '') . ' ' . ($res->json()['realname'] ?? ''))
            : '-';
        return $name;
    }

    /*************************************************
     * Function: getResource
     * Description: mengambil resource dari GLPI API
     *************************************************/
    public function getResource(string $endpoint, int $id, string $token)
    {
        if (!$id) return '-';
        $res = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $token,
        ])->get($this->glpiApiUrl . "/$endpoint/$id");

        return $res->successful() ? ($res->json()) : null;
    }

    /*************************************************
     * Function: getIdByNameSearch
     * Description: mencari nama item/resource dari GLPI API
     *************************************************/
    public function getIdByNameSearch($type, $name)
    {
        $sessionToken = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Content-Type' => 'application/json',
        ])->post($this->glpiApiUrl . '/initSession', [
            'login' => config('glpi.api_user'),
            'password' => config('glpi.api_password'),
        ])->json()['session_token'];

        $deviceName = strtoupper($name);

        // null jika ingin mencari kode aset
        if ($type == null) {
            $type = 'AllAssets';
        }

        // Siapkan parameter
        $params = [
            'criteria[0][field]' => 1,
            'criteria[0][searchtype]' => 'contains',
            'criteria[0][value]' => $deviceName,
            'forcedisplay[0]' => 2, // pastikan "id" tampil
        ];

        $query = http_build_query($params);
        $url = rtrim($this->glpiApiUrl, '/') . "/search/$type?" . $query;

        $res = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->get($url);

        $json = $res->json();
        if (($json['totalcount'] ?? 0) > 0) {
            $data = $json['data'] ?? [];

            // Filter exact match setelah ambil data
            $device = collect($data)->first(function ($item) use ($deviceName) {
                return isset($item[1]) && strcasecmp($item[1], $deviceName) === 0;
            });

            return [
                'id' => $device['2'], // contoh ambil ID device
                'type' => $device['itemtype'] ?? $type,
                // 'type' => $type,
            ];
        } else {
            return [];
        }
    }

    /*************************************************
     * Function: getStatusName
     * Description: mendapatkan nama status tiket dari ID
     *************************************************/
    public function getStatusName($id)
    {
        $status = [
            1 => 'Baru',
            2 => 'Proses',
            3 => 'Proses (Dijadwalkan)',
            4 => 'Tunda',
            5 => 'Selesai',
            6 => 'Tutup',
        ];
        return $status[$id] ?? 'Unknown';
    }

    public function getItemResource($endpoint, $id, $item, $token)
    {
        if (!$id) return '-';
        $res = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $token,
        ])->get($this->glpiApiUrl . "/$endpoint/$id/$item");

        return $res->successful() ? ($res->json()) : '-';
    }
}
