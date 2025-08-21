<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class CheckGLPISession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $glpiApiUrl = config('glpi.api_url');
        $appToken = config('glpi.api_app_token');
        $sessionToken = Session::get('glpi_session_token');

        // 1. Tidak ada token → redirect login
        if (!$sessionToken) {
            Session::put('redirect_after_login', $request->fullUrl());
            return redirect()->route('login.form')->withErrors(['msg' => 'Sesi berakhir, mohon login ulang.']);
        }

        // Ambil timestamp pengecekan terakhir
        $lastCheck = Session::get('glpi_token_checked_at');

        // Kalau belum pernah cek atau sudah lewat 20 menit → cek ke GLPI
        if (!$lastCheck || now()->diffInMinutes($lastCheck) >= 20) {
            $response = Http::withHeaders([
                'App-Token' => $appToken,
                'Session-Token' => $sessionToken,
            ])->get($glpiApiUrl . '/getFullSession');

            if ($response->failed() || isset($response['ERROR'])) {
                Session::put('redirect_after_login', $request->fullUrl());
                Session::forget(['glpi_session_token', 'glpi_token_checked_at']);
                return redirect()->route('login.form')->withErrors(['msg' => 'Sesi berakhir, mohon login ulang.']);
            }

            // Simpan waktu pengecekan terakhir
            Session::put('glpi_token_checked_at', now());
        }

        return $next($request);
    }
}
