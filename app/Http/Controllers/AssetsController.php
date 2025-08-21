<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use App\Helpers\ApiHelper;

class AssetsController extends Controller
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
     * Function: show
     * Description: menampilkan detail perangkat
     *************************************************/
    public function show($deviceName)
    {
        $foundDevice = $this->apiHelper->getIdByNameSearch(null, $deviceName);
        if (!empty($foundDevice)) {
            $deviceId = $foundDevice['id'];
            $deviceType = strtolower($foundDevice['type']);
            if (method_exists($this, $deviceType)) {
                // memanggil function dengan nama sesuai param $type
                return $this->$deviceType($deviceId);
            }
        }
        abort(404);
    }

    public function computer($id)
    {
        $sessionToken = Session::get('glpi_session_token');
        $tempSession = null;
        $deviceType = 'Computer';

        // Jika tidak ada session, gunakan akun khusus
        if (!$sessionToken) {
            // Jika belum login, initSession pakai akun API khusus
            $tempSession = Http::withHeaders([
                'App-Token' => $this->appToken,
                'Content-Type' => 'application/json',
            ])->post($this->glpiApiUrl . '/initSession', [
                'login' => config('glpi.api_user'),
                'password' => config('glpi.api_password'),
            ])->json()['session_token'];
            $sessionToken = $tempSession;
        }

        // Ambil data asset
        $device = $this->apiHelper->getResource($deviceType, $id, $sessionToken);
        if (is_null($device)) {
            abort(404);
        }

        // Model: manufaktur dan model
        $manufacturer = $this->apiHelper->getResource('Manufacturer', $device['manufacturers_id'] ?? null, $sessionToken);
        $model = $this->apiHelper->getResource('ComputerModel', $device['computermodels_id'] ?? null, $sessionToken);
        // Output: $manufacturer['name'], $model['name']

        // OS
        $osId = $this->apiHelper->getItemResource($deviceType, $id, 'Item_OperatingSystem', $sessionToken)[0]['operatingsystems_id'];
        $os = $osId ? $this->apiHelper->getResource('OperatingSystem', $osId, $sessionToken) : null;
        // Output: $os['name']

        // CPU
        $cpuId = $this->apiHelper->getItemResource($deviceType, $id, 'Item_DeviceProcessor', $sessionToken)[0]['deviceprocessors_id'];
        $cpu = $this->apiHelper->getResource('DeviceProcessor', $cpuId, $sessionToken);
        // Output: $cpu['designation']

        // Memory
        $totalMemory = 0;
        $data = $this->apiHelper->getItemResource($deviceType, $id, 'Item_DeviceMemory', $sessionToken);
        foreach ($data as $memory) {
            $memoryId = $memory['devicememories_id'];
            $memoryTypeId = $this->apiHelper->getResource('DeviceMemory', $memoryId, $sessionToken)['devicememorytypes_id'];
            $memoryType = $this->apiHelper->getResource('DeviceMemoryType', $memoryTypeId, $sessionToken);
            $totalMemory += $memory['size'];
        }
        $totalMemory = round($totalMemory / 1024);
        // Output: $memoryType['name'], $totalMemory

        // Storage
        $totalStorage = 0;
        $data = $this->apiHelper->getItemResource($deviceType, $id, 'Item_DeviceHardDrive', $sessionToken);
        foreach ($data as $storage) {
            $hardDriveId = $storage['deviceharddrives_id'];
            $interfaceTypeId = $this->apiHelper->getResource('DeviceHardDrive', $hardDriveId, $sessionToken)['interfacetypes_id'];
            $interfaceType = $this->apiHelper->getResource('InterfaceType', $interfaceTypeId, $sessionToken);
            $totalStorage += $storage['capacity'];
        }
        $totalStorage = round($totalStorage / 1024);
        // Output: $interfaceType['name'], $totalStorage

        // User
        $user = $this->apiHelper->getResource('User', $device['users_id'] ?? null, $sessionToken);
        // Output: $user['name']

        // Lokasi
        $location = $this->apiHelper->getResource('Location', $device['locations_id'] ?? null, $sessionToken);
        // Output: $location['name']

        // Hapus session jika tadi login dengan akun khusus
        if ($tempSession) {
            // Hapus session token sementara
            Http::withHeaders([
                'App-Token' => $this->appToken,
                'Session-Token' => $tempSession,
            ])->get($this->glpiApiUrl . '/killSession');
        }

        $device['model'] = (data_get($manufacturer, 'name', '-') . ' - ' . data_get($model, 'name', '-'));
        $device['os'] = data_get($os, 'name', '-');
        $device['cpu'] = data_get($cpu, 'designation', '-');

        $memoryTypeName = data_get($memoryType, 'name', '-');
        $device['memory'] = $memoryTypeName . ' ' . ($totalMemory ?? 0) . ' GB';

        $interfaceTypeName = data_get($interfaceType, 'name', '-');
        $device['storage'] = $interfaceTypeName . ' ' . ($totalStorage ?? 0) . ' GB';

        $device['user'] = data_get($user, 'name', '-');
        $device['location'] = data_get($location, 'name', '-');
        return view('device.computer', compact('device', 'id'));
    }

    public function printer($id)
    {
        $sessionToken = Session::get('glpi_session_token');
        $tempSession = null;
        $deviceType = 'Printer';

        // Jika tidak ada session, gunakan akun khusus
        if (!$sessionToken) {
            // Jika belum login, initSession pakai akun API khusus
            $tempSession = Http::withHeaders([
                'App-Token' => $this->appToken,
                'Content-Type' => 'application/json',
            ])->post($this->glpiApiUrl . '/initSession', [
                'login' => config('glpi.api_user'),
                'password' => config('glpi.api_password'),
            ])->json()['session_token'];
            $sessionToken = $tempSession;
        }

        // Ambil data asset
        $device = $this->apiHelper->getResource($deviceType, $id, $sessionToken);
        if (is_null($device)) {
            abort(404);
        }

        // Model: manufaktur dan model
        $manufacturer = $this->apiHelper->getResource('Manufacturer', $device['manufacturers_id'] ?? null, $sessionToken);
        $model = $this->apiHelper->getResource('PrinterModel', $device['printermodels_id'] ?? null, $sessionToken);
        // Output: $manufacturer['name'], $model['name']

        // User
        $user = $this->apiHelper->getResource('User', $device['users_id'] ?? null, $sessionToken);
        // Output: $user['name']

        // Lokasi
        $location = $this->apiHelper->getResource('Location', $device['locations_id'] ?? null, $sessionToken);
        // Output: $location['name']

        // Hapus session jika tadi login dengan akun khusus
        if ($tempSession) {
            // Hapus session token sementara
            Http::withHeaders([
                'App-Token' => $this->appToken,
                'Session-Token' => $tempSession,
            ])->get($this->glpiApiUrl . '/killSession');
        }

        $device['manufacturer'] = data_get($manufacturer, 'name', '-');
        $device['model'] = data_get($model, 'name', '-');
        $device['user'] = $user['name'] ?? '-';
        $device['location'] = $location['name'] ?? '-';
        return view('device.general', compact('device', 'id'));
    }

    public function monitor($id)
    {
        $sessionToken = Session::get('glpi_session_token');
        $tempSession = null;
        $deviceType = 'Monitor';

        // Jika tidak ada session, gunakan akun khusus
        if (!$sessionToken) {
            // Jika belum login, initSession pakai akun API khusus
            $tempSession = Http::withHeaders([
                'App-Token' => $this->appToken,
                'Content-Type' => 'application/json',
            ])->post($this->glpiApiUrl . '/initSession', [
                'login' => config('glpi.api_user'),
                'password' => config('glpi.api_password'),
            ])->json()['session_token'];
            $sessionToken = $tempSession;
        }

        // Ambil data asset
        $device = $this->apiHelper->getResource($deviceType, $id, $sessionToken);
        if (is_null($device)) {
            abort(404);
        }

        // Model: manufaktur dan model
        $manufacturer = $this->apiHelper->getResource('Manufacturer', $device['manufacturers_id'] ?? null, $sessionToken);
        $model = $this->apiHelper->getResource('MonitorModel', $device['monitormodels_id'] ?? null, $sessionToken);
        // Output: $manufacturer['name'], $model['name']

        // User
        $user = $this->apiHelper->getResource('User', $device['users_id'] ?? null, $sessionToken);
        // Output: $user['name']

        // Lokasi
        $location = $this->apiHelper->getResource('Location', $device['locations_id'] ?? null, $sessionToken);
        // Output: $location['name']

        // Hapus session jika tadi login dengan akun khusus
        if ($tempSession) {
            Http::withHeaders([
                'App-Token' => $this->appToken,
                'Session-Token' => $tempSession,
            ])->get($this->glpiApiUrl . '/killSession');
        }

        $device['manufacturer'] = data_get($manufacturer, 'name', '-');
        $device['model'] = data_get($model, 'name', '-');
        $device['user'] = $user['name'] ?? '-';
        $device['location'] = $location['name'] ?? '-';
        return view('device.general', compact('device', 'id'));
    }

    /*************************************************
     * Function: info
     * Description: mengambil detail perangkat
     *************************************************/
    public function info($deviceName)
    {
        $sessionToken = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Content-Type' => 'application/json',
        ])->post($this->glpiApiUrl . '/initSession', [
            'login' => config('glpi.api_user'),
            'password' => config('glpi.api_password'),
        ])->json()['session_token'];

        $foundDevice = $this->apiHelper->getIdByNameSearch(null, $deviceName);

        if (!$foundDevice) {
            return [
                'status' => 'error',
                'message' => 'Data Aset tidak ditemukan',
                'data' => null,
            ];
        }
        $deviceId = $foundDevice['id'];
        $deviceType = $foundDevice['type'];

        $response = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->get($this->glpiApiUrl . "/$deviceType/$deviceId");
        $data = $response->json();

        // Remap ITIL Category bedasarkan tipe Perangkat
        if ($deviceType === 'Monitor') {
            $itilCategory = 'Hardware';
        } else {
            $itilCategory = $deviceType;
        }

        $category = $this->apiHelper->getIdByNameSearch('ITILCategory', $itilCategory);
        $data = array_merge($data, ['itilcategoryid' => $category['id']]);

        Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->get($this->glpiApiUrl . '/killSession');

        return [
            'status' => 'success',
            'message' => 'Data Aset ditemukan',
            'data' => $data,
        ];
    }
}
