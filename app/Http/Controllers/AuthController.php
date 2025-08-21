<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    protected $glpiApiUrl;
    protected $appToken;

    public function __construct()
    {
        $this->glpiApiUrl = config('glpi.api_url');
        $this->appToken = config('glpi.api_app_token');
    }

    /*************************************************
     * Function: showLoginForm
     * Description: menampilkan form login
     *************************************************/
    public function showLoginForm()
    {
        return view('login');
    }

    /*************************************************
     * Function: login
     * Description: proses login dengan GLPI API
     *************************************************/
    public function login(Request $request)
    {
        $errorMessage = [
            'required' => 'Kolom :attribute harus diisi.',
        ];
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], $errorMessage);

        // 1️⃣ Login ke GLPI API untuk dapat session_token
        $response = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Content-Type' => 'application/json',
        ])->post($this->glpiApiUrl . '/initSession', [
            'login' => $request->username,
            'password' => $request->password,
        ]);

        if (!$response->successful()) {
            return back()->withErrors(['login' => 'Login gagal, username & password tidak sesuai.']);
        }

        $data = $response->json();
        $sessionToken = $data['session_token'];

        // 2️⃣ Ambil informasi user (id, name, profile)
        $userResponse = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->get($this->glpiApiUrl . '/getFullSession');

        if (!$userResponse->successful()) {
            return back()->withErrors(['login' => 'Gagal mengambil profil user.']);
        }

        $userData = $userResponse->json();

        // Ambil ID user pertama (bisa disesuaikan kalau banyak profile)
        $userId = $userData['session']['glpiID'] ?? null;
        $userName = $userData['session']['glpiname'] ?? $request->username;

        $userProfile = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->get($this->glpiApiUrl . "/getActiveProfile")->json()['active_profile']['name'] ?? [];

        // 3️⃣ Simpan ke session Laravel
        Session::put('glpi_session_token', $sessionToken);
        Session::put('glpi_user_id', $userId);
        Session::put('glpi_user_name', $userName);
        Session::put('glpi_user_profile', $userProfile);

        // 4️⃣ Redirect jika ada
        if (in_array($request->password, ['API2025', 'API@2025'])) {
            return redirect()->route('profile.reset_password')->with('success', 'Mohon ganti password Anda.');
        }
        $redirect = Session::pull('redirect_after_login');
        if ($redirect) {
            return redirect($redirect);
        }

        return redirect()->route('ticket.index')->with('success', 'Berhasil login');
    }

    /*************************************************
     * Function: logout
     * Description: proses logout dari GLPI
     *************************************************/
    public function logout()
    {
        $response = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Content-Type' => 'application/json',
        ])->get($this->glpiApiUrl . '/killSession');
        Session::flush();
        return redirect()->route('login.form');
    }
}
