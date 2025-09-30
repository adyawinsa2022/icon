<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CopierController extends Controller
{
    public function index()
    {
        // Pastikan file sudah ada
        if (!Storage::disk('local')->exists('copier.json')) {
            abort(404);
        }

        // Ambil isi file JSON dari storage/app/copier.json
        $json = json_decode(Storage::disk('local')->get('copier.json'), true);

        return view('copier.index', [
            'date' => $json['date'] ?? null,
            'data' => $json['data'] ?? []
        ]);
    }
}
