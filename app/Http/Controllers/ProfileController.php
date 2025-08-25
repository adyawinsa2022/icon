<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ProfileController extends Controller
{
    protected $glpiApiUrl;
    protected $appToken;

    public function __construct()
    {
        $this->glpiApiUrl = config('glpi.api_url');
        $this->appToken = config('glpi.api_app_token');
    }

    public function index()
    {
        $userId = Session::get('glpi_user_id');
        $sessionToken = Session::get('glpi_session_token');

        $user = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->get($this->glpiApiUrl . "/User/$userId")->json();

        $user['name'] = Session::get('glpi_user_name');

        return view('profile.index', compact('user'));
    }

    public function showResetPassword()
    {
        $userId = Session::get('glpi_user_id');
        return view('profile.password', compact('userId'));
    }

    public function resetPassword(Request $request)
    {
        $sessionToken = Session::get('glpi_session_token');
        $userId = Session::get('glpi_user_id');

        $errorMessage = [
            'required' => 'Kolom :attribute harus diisi.',
            'confirmed' => 'Password tidak sesuai.',
            'min' => 'Password minimal :min karakter.',
            'regex' => 'Password harus mengandung minimal satu angka.',
        ];
        $request->validate([
            'password' => 'required|string|confirmed|min:8|regex:/[0-9]/',
        ], $errorMessage);

        // Login akun API khusus dengan permission update User
        $tempSession = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Content-Type' => 'application/json',
        ])->post($this->glpiApiUrl . '/initSession', [
            'login' => config('glpi.api_user'),
            'password' => config('glpi.api_password'),
        ])->json();

        $response = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $tempSession['session_token'],
            'Content-Type' => 'application/json',
        ])->put($this->glpiApiUrl . "/User/$userId", [
            'input' => [
                'password' => $request->password,
                'password2' => $request->password_confirmation,
            ]
        ]);

        Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $tempSession['session_token'],
        ])->get($this->glpiApiUrl . '/killSession');

        if (!$response->successful()) {
            return back()->withErrors(['msg' => 'Gagal reset password']);
        }

        $user = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->get($this->glpiApiUrl . "/User/$userId");

        $redirect = Session::pull('redirect_after_login');
        if ($redirect) {
            return redirect($redirect);
        }

        return redirect()->route('profile.index', compact('user'))->with('success', 'Reset Password berhasil');
    }
}
